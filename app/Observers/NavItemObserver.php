<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\NavItem;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Invalidate cache menu navigasi setiap kali NavItem berubah.
 */
final class NavItemObserver
{
    public function saved(NavItem $item): void
    {
        Cache::forget(CacheKeys::NAV_MENU);
    }

    public function deleted(NavItem $item): void
    {
        Cache::forget(CacheKeys::NAV_MENU);
    }
}
