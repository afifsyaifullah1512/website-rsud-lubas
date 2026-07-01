<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk model {@see SiteSetting}. Bertugas meng-invalidasi cache
 * `site_settings` setiap kali entri berubah, sehingga halaman publik selalu
 * menampilkan nilai terbaru segera setelah Admin menyimpan perubahan.
 *
 * Memenuhi requirement 26.3.
 */
final class SiteSettingObserver
{
    public function saved(SiteSetting $setting): void
    {
        $this->flush();
    }

    public function deleted(SiteSetting $setting): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(SiteSettingService::CACHE_KEY);

        // Reset memoisasi pada instance singleton (jika sudah di-resolve) agar
        // request yang sedang berjalan langsung mengambil nilai terbaru pada
        // pemanggilan berikutnya.
        if (app()->resolved(SiteSettingService::class)) {
            app(SiteSettingService::class)->forget();
        }
    }
}
