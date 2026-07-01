<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\NotifyAdminComplaintJob;
use App\Models\Complaint;
use App\Models\ComplaintLog;
use App\Models\User;
use App\Support\Enums\ComplaintStatus;
use App\Support\ValueObjects\ComplaintData;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Throwable;

/**
 * Layanan domain untuk pengaduan publik.
 *
 * Implementasi mengikuti Algoritma 3 di `design.md`:
 *  - Rate limit 3 submit / IP / 1 jam (Requirement 11.9, properti P11).
 *  - Generate `ticket_number` unik dengan format
 *    `RSUD-YYYYMMDD-XXXXXX` (Requirement 11.5, properti P5). Retry
 *    sampai unique pada level DB (race-safe via try/catch
 *    UniqueConstraintViolationException).
 *  - Sanitize `message` via HTMLPurifier sebelum disimpan
 *    (Requirement 11.10, 30.2).
 *  - Catat 1 baris `complaint_logs` saat dibuat (Requirement 11.4,
 *    24.2).
 *  - Dispatch `NotifyAdminComplaintJob` ke queue tanpa memblokir
 *    HTTP response (Requirement 34.1).
 *  - Validasi transisi status `NEW → IN_REVIEW → RESPONDED → CLOSED`
 *    di `changeStatus` (Requirement 24.3); super-admin bebas pindah ke
 *    `CLOSED`.
 *
 * Validates: Requirements 11.3, 11.4, 11.5, 11.9, 11.10, 24.2, 24.3,
 * 32.1, 32.2, 34.1.
 */
final class ComplaintService
{
    /** Maksimal pengaduan per IP per jam (Requirement 11.9). */
    public const RATE_LIMIT_MAX = 3;
    public const RATE_LIMIT_WINDOW_SECONDS = 3600;

    /** Batas iterasi pencarian ticket unik (untuk safety). */
    private const TICKET_MAX_ATTEMPTS = 5;

    /**
     * Submit pengaduan baru.
     *
     * @throws ThrottleRequestsException Jika IP melewati rate limit.
     * @throws Throwable                  Jika gagal generate ticket unik.
     */
    public function submit(ComplaintData $data, string $ip): Complaint
    {
        $rateKey = $this->rateLimiterKey($ip);

        if (RateLimiter::tooManyAttempts($rateKey, self::RATE_LIMIT_MAX)) {
            $retryAfter = RateLimiter::availableIn($rateKey);
            throw new ThrottleRequestsException(
                'Terlalu banyak pengaduan dari IP ini. Coba lagi dalam '
                .$retryAfter.' detik.'
            );
        }

        $sanitizedMessage = $this->sanitizeMessage($data->message);

        $complaint = $this->createWithUniqueTicket(
            $data,
            $sanitizedMessage,
            $ip,
        );

        RateLimiter::hit($rateKey, self::RATE_LIMIT_WINDOW_SECONDS);

        // Dispatch job notifikasi admin tanpa memblokir response.
        Queue::push(new NotifyAdminComplaintJob($complaint->id));

        return $complaint;
    }

    /**
     * Ubah status Complaint dengan validasi transisi.
     *
     * @throws DomainException Jika transisi tidak diperbolehkan.
     */
    public function changeStatus(
        Complaint $complaint,
        ComplaintStatus $next,
        ?string $note,
        ?User $actor = null,
    ): void {
        $current = $complaint->status;

        $isSuperAdmin = $actor !== null
            && method_exists($actor, 'hasRole')
            && $actor->hasRole('super-admin');

        $allowed = $current->canTransitionTo($next)
            || ($isSuperAdmin && $next === ComplaintStatus::CLOSED);

        if (! $allowed) {
            throw new DomainException(sprintf(
                'Transisi status %s → %s tidak diperbolehkan.',
                $current->value,
                $next->value,
            ));
        }

        DB::transaction(function () use ($complaint, $next, $note, $actor): void {
            $complaint->status = $next;
            $complaint->save();

            ComplaintLog::query()->create([
                'complaint_id' => $complaint->id,
                'user_id' => $actor?->id,
                'status' => $next,
                'note' => $note,
            ]);
        });
    }

    /**
     * Buat ticket dan insert Complaint dengan retry pada bentrok unique.
     *
     * @throws Throwable Jika tidak dapat mendapatkan ticket unik
     *                   setelah {@see self::TICKET_MAX_ATTEMPTS} percobaan.
     */
    private function createWithUniqueTicket(
        ComplaintData $data,
        string $sanitizedMessage,
        string $ip,
    ): Complaint {
        $attempt = 0;

        while (true) {
            $ticket = $this->generateTicketNumber();
            try {
                return DB::transaction(function () use ($data, $sanitizedMessage, $ip, $ticket): Complaint {
                    /** @var Complaint $complaint */
                    $complaint = Complaint::query()->create([
                        'ticket_number' => $ticket,
                        'name' => $data->name,
                        'email' => $data->email,
                        'phone' => $data->phone,
                        'subject' => $data->subject,
                        'message' => $sanitizedMessage,
                        'status' => ComplaintStatus::NEW,
                        'ip_address' => $ip,
                    ]);

                    ComplaintLog::query()->create([
                        'complaint_id' => $complaint->id,
                        'user_id' => null,
                        'status' => ComplaintStatus::NEW,
                        'note' => 'Pengaduan masuk',
                    ]);

                    return $complaint;
                });
            } catch (UniqueConstraintViolationException $e) {
                if (++$attempt >= self::TICKET_MAX_ATTEMPTS) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Generate `RSUD-YYYYMMDD-XXXXXX`. `XXXXXX` adalah 6 karakter
     * uppercase A-Z 0-9.
     */
    public function generateTicketNumber(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(6));
        // Str::random dapat menghasilkan karakter lowercase; pastikan
        // hanya A-Z 0-9 untuk memenuhi regex P5.
        $random = preg_replace('/[^A-Z0-9]/', '0', $random) ?? '000000';
        if (strlen($random) < 6) {
            $random = str_pad($random, 6, '0');
        } elseif (strlen($random) > 6) {
            $random = substr($random, 0, 6);
        }

        return "RSUD-{$date}-{$random}";
    }

    /**
     * Sanitize body pengaduan via HTMLPurifier.
     */
    private function sanitizeMessage(string $message): string
    {
        // Untuk body pengaduan, izinkan plain text saja: strip seluruh
        // tag HTML. Purifier dengan profile 'youtube' atau default
        // tidak cocok karena kita ingin teks polos.
        if (class_exists(Purifier::class)) {
            try {
                /** @var string $clean */
                $clean = Purifier::clean(strip_tags($message));

                return $clean;
            } catch (Throwable) {
                // fallthrough to strip_tags fallback
            }
        }

        return strip_tags($message);
    }

    private function rateLimiterKey(string $ip): string
    {
        return 'complaint:'.$ip;
    }
}
