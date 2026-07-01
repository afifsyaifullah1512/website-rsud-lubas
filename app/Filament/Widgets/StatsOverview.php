<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\JobVacancy;
use App\Models\News;
use App\Support\Enums\ComplaintStatus;
use App\Support\Enums\JobVacancyStatus;
use App\Support\Enums\NewsStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Dashboard stats overview untuk admin.
 *
 * Memenuhi Requirement 24.1 (statistik pengaduan dll).
 */
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Berita Published', News::query()->where('status', NewsStatus::PUBLISHED)->count())
                ->description('Total berita aktif')
                ->color('success'),
            Stat::make('Pengaduan Baru', Complaint::query()->where('status', ComplaintStatus::NEW)->count())
                ->description('Belum diproses')
                ->color('warning'),
            Stat::make('Lowongan Terbuka', JobVacancy::query()->where('status', JobVacancyStatus::OPEN)->count())
                ->description('Periode aktif')
                ->color('primary'),
            Stat::make('Dokter Aktif', Doctor::query()->where('is_active', true)->count())
                ->description('Dokter terdaftar')
                ->color('info'),
        ];
    }
}
