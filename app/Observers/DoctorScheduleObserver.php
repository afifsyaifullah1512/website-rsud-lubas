<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DoctorSchedule;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk {@see DoctorSchedule}.
 *
 * Setiap perubahan jadwal harus meng-invalidasi cache listing jadwal
 * dan halaman beranda (Requirement 1.6, 18.5, P9).
 */
final class DoctorScheduleObserver
{
    public function saved(DoctorSchedule $schedule): void
    {
        $this->flush();
    }

    public function deleted(DoctorSchedule $schedule): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(CacheKeys::HOME);
        Cache::forget(CacheKeys::schedules(md5(serialize([
            'polyclinicId' => null,
            'day'          => null,
            'search'       => null,
        ]))));
    }
}
