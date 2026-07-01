<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Doctor;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk model {@see Doctor}.
 *
 * Doctor mempengaruhi cache `schedules:*` (jadwal dokter) dan halaman
 * beranda (Requirement 1.6, 18.5).
 */
final class DoctorObserver
{
    public function saved(Doctor $doctor): void
    {
        $this->flush();
    }

    public function deleted(Doctor $doctor): void
    {
        $this->flush();
    }

    public function restored(Doctor $doctor): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(CacheKeys::HOME);

        // Cache listing jadwal di-key dengan hash filter; pendekatan
        // pragmatis untuk driver tanpa tag adalah `Cache::flush()` —
        // tetapi itu menghapus seluruh cache. Sebaliknya, kita kosongkan
        // key umum yang dipakai service untuk skenario default & tinggalkan
        // entri terfilter biarkan kedaluwarsa via TTL 10 menit
        // (Requirement 29.5). Untuk halaman yang menampilkan daftar dokter
        // tanpa filter, key default = md5(serialize([null,null,null])).
        Cache::forget(CacheKeys::schedules(md5(serialize([
            'polyclinicId' => null,
            'day'          => null,
            'search'       => null,
        ]))));
    }
}
