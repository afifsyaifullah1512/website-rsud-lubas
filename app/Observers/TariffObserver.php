<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tariff;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk {@see Tariff}.
 *
 * Tarif dipakai oleh halaman `/tarif`. Requirement 21.3, P9.
 */
final class TariffObserver
{
    public function saved(Tariff $tariff): void
    {
        Cache::forget(CacheKeys::TARIFFS_INDEX);
    }

    public function deleted(Tariff $tariff): void
    {
        Cache::forget(CacheKeys::TARIFFS_INDEX);
    }
}
