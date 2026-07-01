<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Service untuk mengelola pengaturan situs (Site Settings) dengan dua lapis cache:
 *
 *  1. **Cache application-level (Cache::rememberForever)** dengan key `site_settings`
 *     yang berisi seluruh map key→value. Cache ini hanya di-invalidasi ketika
 *     terjadi mutasi (`set()`/observer `saved`/`deleted`).
 *  2. **Memoisasi instance** (`$cached`) sehingga dalam satu request HTTP
 *     pemuatan cache application-level hanya terjadi paling banyak satu kali.
 *     Hal ini bekerja bersama binding `singleton` di service container.
 *
 * @see \App\Providers\AppServiceProvider untuk binding singleton dan registrasi observer.
 *
 * Memenuhi:
 *  - 12.1, 12.2, 12.3 (Kontak menampilkan data Site_Setting)
 *  - 26.1, 26.2 (cache permanen, dimuat satu kali per request)
 *  - 26.3 (invalidate cache saat update)
 */
final class SiteSettingService
{
    /**
     * Key cache application-level untuk seluruh pengaturan situs.
     */
    public const CACHE_KEY = 'site_settings';

    /**
     * Memoisasi map key→value untuk request berjalan.
     *
     * @var array<string,mixed>|null
     */
    private ?array $cached = null;

    /**
     * Ambil nilai pengaturan berdasarkan key.
     *
     * @template TDefault
     *
     * @param  TDefault  $default
     * @return mixed|TDefault
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Simpan/perbarui nilai pengaturan dan invalidate cache.
     *
     * Observer `SiteSettingObserver` juga akan men-flush cache saat event
     * `saved`/`deleted` ter-trigger; metode ini tetap melakukan flush eksplisit
     * agar invalidasi terjadi meski observer tidak terpasang (mis. unit test
     * yang menggunakan service tanpa Eloquent events).
     */
    public function set(string $key, mixed $value): void
    {
        if ($value === null) {
            // Kolom value NOT NULL — hapus row jika value null, atau simpan ''
            SiteSetting::query()->where('key', $key)->delete();
        } else {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        $this->forget();
    }

    /**
     * Kembalikan seluruh pengaturan sebagai map key→value.
     *
     * @return array<string,mixed>
     */
    public function all(): array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        /** @var array<string,mixed> $values */
        $values = Cache::rememberForever(
            self::CACHE_KEY,
            // Hydrate full models agar attribute cast (`value` -> array) berlaku;
            // Builder::pluck() melewati cast karena tidak meng-hydrate model.
            static fn (): array => SiteSetting::query()
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->all(),
        );

        return $this->cached = $values;
    }

    /**
     * Invalidate cache application-level dan memoisasi instance.
     */
    public function forget(): void
    {
        $this->cached = null;
        Cache::forget(self::CACHE_KEY);
    }
}
