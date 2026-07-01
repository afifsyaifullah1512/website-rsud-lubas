<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\NewComplaintNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Notifikasi pengaduan baru ke seluruh Admin_User berperan
 * `petugas-pengaduan` (dan/atau `super-admin`).
 *
 * Memenuhi Requirements 11.4 (kirim notifikasi ke role
 * petugas-pengaduan), 34.1 (asynchronous), 34.2 (retry 3x backoff
 * exponential, gagal akhir → `failed_jobs`).
 *
 * Job dibuat sebagai `ShouldQueue` agar antrian queue (Redis/database)
 * yang menjalankannya, bukan request HTTP. Backoff `[10, 30, 60]`
 * detik mengikuti pola exponential ringan.
 */
class NotifyAdminComplaintJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Maksimal percobaan eksekusi job (Requirement 34.2). */
    public int $tries = 3;

    /**
     * Backoff (detik) antar percobaan — exponential ringan.
     *
     * @return array<int,int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public readonly int $complaintId,
    ) {
    }

    public function handle(): void
    {
        /** @var Complaint|null $complaint */
        $complaint = Complaint::query()->find($this->complaintId);
        if ($complaint === null) {
            return;
        }

        $recipients = User::query()
            ->whereHas('roles', function ($q): void {
                $q->whereIn('name', ['petugas-pengaduan', 'super-admin']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new NewComplaintNotification($complaint));
    }
}
