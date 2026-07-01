<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\JobVacancy;
use App\Support\Enums\JobVacancyStatus;
use Illuminate\Console\Command;

/**
 * Auto-derive status JobVacancy → CLOSED bila `today > close_at`
 * (Requirement 9.4, 22.3).
 */
class AutoCloseVacancy extends Command
{
    protected $signature = 'vacancy:auto-close';

    protected $description = 'Tandai JobVacancy sebagai CLOSED bila tanggal tutup sudah lewat.';

    public function handle(): int
    {
        $today = now()->toDateString();

        $count = JobVacancy::query()
            ->where('status', JobVacancyStatus::OPEN)
            ->whereDate('close_at', '<', $today)
            ->update(['status' => JobVacancyStatus::CLOSED->value]);

        $this->info("Closed {$count} lowongan.");

        return self::SUCCESS;
    }
}
