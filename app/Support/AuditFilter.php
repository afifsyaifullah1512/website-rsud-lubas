<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditFilter — penyaring PII / data sensitif untuk audit log.
 *
 * Validates: Requirements 15.6, 24.5, 30.7, 32.2.
 *
 * Layer pertahanan kedua di atas konfigurasi `LogsActivity` per-model
 * (`logExcept(...)`). Kelas ini menyediakan utilitas reusable bagi
 * service layer / observer / job yang membentuk array properti
 * sebelum disimpan ke `activity_log` agar PII tidak ikut bocor.
 *
 * Daftar key PII yang di-redact:
 * - `message`         → isi pengaduan (Complaint), Requirement 24.5
 * - `email`           → email pengadu / user
 * - `phone`           → nomor telepon pengadu
 * - `ip_address`      → IP submitter
 * - `password`        → kredensial User (Requirement 30.7)
 * - `remember_token`  → token sesi User (Requirement 30.7)
 *
 * Model yang relevan:
 * - {@see \App\Models\Complaint}      — sudah `logExcept` PII
 * - {@see \App\Models\ComplaintLog}   — note dapat berisi PII
 * - {@see \App\Models\User}           — `logExcept` password
 *
 * Catatan: kelas ini stateless dan murni — semua method statis,
 * tanpa side-effect, sehingga aman dipanggil dari banyak konteks.
 */
final class AuditFilter
{
    /**
     * Daftar key yang dianggap PII / sensitif dan WAJIB di-redact
     * sebelum masuk ke audit log.
     *
     * @var list<string>
     */
    public const PII_KEYS = [
        'message',
        'email',
        'phone',
        'ip_address',
        'password',
        'remember_token',
    ];

    /**
     * Placeholder yang menggantikan nilai PII di audit log.
     */
    public const REDACTED = '[REDACTED]';

    /**
     * Redact nilai PII dalam array properti audit.
     *
     * Untuk setiap key yang termasuk {@see self::PII_KEYS}, nilai
     * diganti dengan {@see self::REDACTED}. Key non-PII dibiarkan
     * apa adanya, termasuk struktur nested `old`/`attributes` yang
     * dipakai Spatie Activitylog.
     *
     * @param  array<string,mixed>  $properties
     * @return array<string,mixed>
     */
    public static function redactPii(array $properties): array
    {
        foreach ($properties as $key => $value) {
            if (in_array($key, self::PII_KEYS, true)) {
                $properties[$key] = self::REDACTED;

                continue;
            }

            if (is_array($value)) {
                $properties[$key] = self::redactPii($value);
            }
        }

        return $properties;
    }

    /**
     * Ambil dirty attributes dari model setelah menyaring kunci PII.
     * Berguna untuk membentuk payload `properties` audit secara manual
     * (mis. dari service layer ketika model tidak di-tracking via trait).
     *
     * @return array<string,mixed>
     */
    public static function safeAttributes(Model $model): array
    {
        return self::redactPii($model->getDirty());
    }

    /**
     * Reduksi data Complaint untuk audit log: hanya identifier yang
     * non-PII (id + ticket_number). Hindari memuat name/email/phone
     * meskipun key-nya berbeda — sebagai pertahanan ekstra.
     *
     * @return array{id:int|null, ticket_number:string|null}
     */
    public static function forComplaint(Complaint $c): array
    {
        return [
            'id' => $c->id,
            'ticket_number' => $c->ticket_number,
        ];
    }
}
