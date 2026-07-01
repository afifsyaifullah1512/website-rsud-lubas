<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Standarisasi kunci cache aplikasi.
 *
 * Helper ini dipakai oleh Service & Observer untuk menjaga konsistensi
 * nama key sehingga invalidasi cache tetap bersih ketika resource
 * dimutasi melalui Admin_Panel (Requirements 1.6, 6.2, 16.4, 17.3,
 * 18.5, 19.4, 21.3, 26.3 — properti P9 Cache Coherence).
 *
 * Konvensi:
 *   - prefix per-resource diakhiri ":" (mis. `schedules:`)
 *   - key statis (tunggal) memakai snake_case (`site_settings`, `home`)
 *   - tag opsional disediakan agar `cache.tags()` dapat dipakai
 *     pada driver yang mendukung (redis/memcached).
 */
final class CacheKeys
{
    /** Halaman beranda publik (TTL 5 menit) — Requirement 1.5, 1.6. */
    public const HOME = 'home';

    /** Map seluruh Site_Setting (forever) — Requirement 26.2. */
    public const SITE_SETTINGS = 'site_settings';

    /** Tree menu navigasi publik (cached forever, di-invalidate via observer). */
    public const NAV_MENU = 'nav_menu_tree';

    /** Prefix listing jadwal dokter terfilter — Requirement 4.8, 29.5. */
    public const SCHEDULES_PREFIX = 'schedules:';

    /** Prefix listing berita publik (paginate) — Requirement 5.1, 5.4. */
    public const NEWS_PREFIX = 'news:';

    /** Prefix detail berita per slug — Requirement 5.5. */
    public const NEWS_SLUG_PREFIX = 'news:slug:';

    /** Listing layanan publik (group by type) — Requirement 3.1. */
    public const SERVICES_INDEX = 'services:index';

    /** Detail layanan publik per slug — Requirement 3.2. */
    public const SERVICE_SLUG_PREFIX = 'services:slug:';

    /** Daftar tarif (dikelompokkan per service & class) — Requirement 8.1. */
    public const TARIFFS_INDEX = 'tariffs:index';

    /** Halaman profil per slug — Requirement 2.1, 16.4. */
    public const PAGE_SLUG_PREFIX = 'pages:slug:';

    /**
     * Tag cache yang dipakai oleh halaman publik (untuk driver yang
     * mendukung tagging). Driver `file` dan `database` mengabaikan
     * tag tetapi tetap aman dipanggil melalui `cache()->tags(...)`
     * yang akan throw — gunakan helper ini hanya pada driver yang
     * support (redis/memcached).
     *
     * @return array<int,string>
     */
    public static function publicTags(): array
    {
        return ['public'];
    }

    /**
     * Bangun key listing jadwal dokter dari hash filter.
     */
    public static function schedules(string $hash): string
    {
        return self::SCHEDULES_PREFIX.$hash;
    }

    public static function newsSlug(string $slug): string
    {
        return self::NEWS_SLUG_PREFIX.$slug;
    }

    public static function serviceSlug(string $slug): string
    {
        return self::SERVICE_SLUG_PREFIX.$slug;
    }

    public static function pageSlug(string $slug): string
    {
        return self::PAGE_SLUG_PREFIX.$slug;
    }
}
