<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\HeroSlide;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk model {@see HeroSlide} — meng-invalidasi cache beranda
 * setiap kali ada mutasi slide.
 *
 * Memenuhi Requirement 36.4 / 1.6 (property P9 Cache Coherence). Karena
 * hook ini bekerja pada event `saved`/`deleted` model, ia mencakup
 * SELURUH jalur mutasi yang dipakai HeroSlideResource:
 *   - create/update slide (Create/Edit page),
 *   - reorder drag-and-drop (`->reorderable('sort_order')`),
 *   - toggle `is_active` inline pada tabel (`ToggleColumn`),
 *   - hapus slide (single & bulk).
 *
 * Cache yang di-flush:
 *   - `home`        — kunci kanonik halaman beranda ({@see CacheKeys::HOME}),
 *   - `home:payload`— payload beranda (slides + fallback) sesuai sketsa desain.
 */
final class HeroSlideObserver
{
    public function saved(HeroSlide $heroSlide): void
    {
        $this->flush();
    }

    public function deleted(HeroSlide $heroSlide): void
    {
        $this->flush();
    }

    public function restored(HeroSlide $heroSlide): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(CacheKeys::HOME);
        Cache::forget('home:payload');
    }
}
