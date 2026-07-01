<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Service;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * Observer untuk {@see Service}.
 *
 * Layanan dipakai oleh `/layanan`, `/layanan/{slug}`, dan blok layanan
 * unggulan di beranda. Requirement 17.3, P9.
 */
final class ServiceObserver
{
    public function saved(Service $service): void
    {
        $this->flush($service);
    }

    public function deleted(Service $service): void
    {
        $this->flush($service);
    }

    public function restored(Service $service): void
    {
        $this->flush($service);
    }

    private function flush(Service $service): void
    {
        Cache::forget(CacheKeys::HOME);
        Cache::forget(CacheKeys::SERVICES_INDEX);

        if (! empty($service->slug)) {
            Cache::forget(CacheKeys::serviceSlug((string) $service->slug));
        }
        $original = $service->getOriginal('slug');
        if (is_string($original) && $original !== '' && $original !== $service->slug) {
            Cache::forget(CacheKeys::serviceSlug($original));
        }
    }
}
