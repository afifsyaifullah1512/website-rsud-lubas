<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Polyclinic;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk {@see Polyclinic}.
 *
 * Polyclinic dipakai oleh halaman layanan, jadwal, dan beranda.
 * Requirement 17.3, P9.
 */
final class PolyclinicObserver
{
    public function saved(Polyclinic $polyclinic): void
    {
        $this->flush();
    }

    public function deleted(Polyclinic $polyclinic): void
    {
        $this->flush();
    }

    public function restored(Polyclinic $polyclinic): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(CacheKeys::HOME);
        Cache::forget(CacheKeys::SERVICES_INDEX);
        Cache::forget(CacheKeys::schedules(md5(serialize([
            'polyclinicId' => null,
            'day'          => null,
            'search'       => null,
        ]))));
    }
}
