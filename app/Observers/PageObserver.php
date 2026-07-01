<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Page;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk {@see Page}.
 *
 * Setiap perubahan Page (sejarah, visi-misi, struktur, sambutan,
 * pendaftaran, dll.) menyebabkan invalidasi cache halaman terkait
 * sesuai slug. Requirement 16.4, P9.
 */
final class PageObserver
{
    public function saved(Page $page): void
    {
        $this->flush($page);
    }

    public function deleted(Page $page): void
    {
        $this->flush($page);
    }

    private function flush(Page $page): void
    {
        if (! empty($page->slug)) {
            Cache::forget(CacheKeys::pageSlug((string) $page->slug));
        }
        $original = $page->getOriginal('slug');
        if (is_string($original) && $original !== '' && $original !== $page->slug) {
            Cache::forget(CacheKeys::pageSlug($original));
        }
    }
}
