<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\News;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk model {@see News} — meng-invalidasi cache halaman
 * publik yang bergantung pada berita ketika berita berubah.
 *
 * Memenuhi Requirement 1.6, 5.x, 6.2, 19.4, dan property P9
 * (Cache Coherence). Aksi:
 *   - cache halaman beranda di-flush (`home`)
 *   - cache slug per-berita di-flush (`news:slug:<slug>`)
 *   - cache listing berita generik (`news:`) di-flush via prefix
 *     (untuk driver yang mendukung `Cache::flush()` selektif kita
 *     memilih mendaftar key umum `news:index` saja agar tetap aman
 *     di driver `file`/`database`).
 */
final class NewsObserver
{
    public function saved(News $news): void
    {
        $this->flush($news);
    }

    public function deleted(News $news): void
    {
        $this->flush($news);
    }

    public function restored(News $news): void
    {
        $this->flush($news);
    }

    private function flush(News $news): void
    {
        Cache::forget(CacheKeys::HOME);
        Cache::forget(CacheKeys::NEWS_PREFIX.'index');

        if (! empty($news->slug)) {
            Cache::forget(CacheKeys::newsSlug((string) $news->slug));
        }

        // Slug lama (jika berubah) — pastikan halaman lama
        // juga terinvalidasi.
        $original = $news->getOriginal('slug');
        if (is_string($original) && $original !== '' && $original !== $news->slug) {
            Cache::forget(CacheKeys::newsSlug($original));
        }
    }
}
