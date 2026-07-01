<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\News;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Layanan domain untuk berita publik.
 *
 * Validates: Requirements 1.2, 5.1–5.8, 19.4.
 *
 * Catatan:
 *  - `paginatePublished` adalah satu-satunya entry untuk listing publik
 *    di Public_Site. Implementasi memastikan visibility hanya untuk
 *    `status = PUBLISHED ∧ published_at <= now()` (P4) dan urutan
 *    `published_at DESC` (Requirement 5.3).
 *  - `findBySlug` mengembalikan instance lengkap dengan relasi
 *    `category` + `author` + `media` (Anti-N+1 — Requirement 29.2).
 *  - `incrementViews` menggunakan `increment` atomic agar aman dari
 *    race-condition di banyak request paralel.
 */
class NewsService
{
    public const PER_PAGE_DEFAULT = 9;
    public const PER_PAGE_MIN = 1;
    public const PER_PAGE_MAX = 50;

    public function __construct(
        private readonly CacheRepository $cache,
    ) {
    }

    /**
     * Daftar News PUBLISHED, opsional difilter kategori, paginate.
     *
     * @throws InvalidArgumentException Jika `$perPage` di luar 1..50.
     */
    public function paginatePublished(
        int $perPage = self::PER_PAGE_DEFAULT,
        ?string $categorySlug = null,
        ?string $search = null,
    ): LengthAwarePaginator {
        if ($perPage < self::PER_PAGE_MIN || $perPage > self::PER_PAGE_MAX) {
            throw new InvalidArgumentException(
                'perPage harus berada pada rentang 1..50.'
            );
        }

        $search = is_string($search) ? trim($search) : null;
        if ($search !== null && mb_strlen($search) < 2) {
            $search = null;
        }

        return News::query()
            ->with(['category', 'author'])
            ->published()
            ->when(
                $categorySlug !== null,
                fn (Builder $q) => $q->whereHas(
                    'category',
                    fn (Builder $c) => $c->where('slug', $categorySlug)
                )
            )
            ->when(
                $search !== null,
                function (Builder $q) use ($search): void {
                    $term = '%'.$search.'%';
                    $q->where(function (Builder $w) use ($term): void {
                        $w->where('title', 'LIKE', $term)
                            ->orWhere('excerpt', 'LIKE', $term);
                    });
                }
            )
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Cari News yang sudah dipublikasikan berdasarkan slug.
     *
     * Mengembalikan `null` jika tidak ditemukan, masih DRAFT, di-archive,
     * atau `published_at` di masa depan / null (Requirement 5.7, P4).
     */
    public function findBySlug(string $slug): ?News
    {
        if (trim($slug) === '') {
            return null;
        }

        return News::query()
            ->with(['category', 'author', 'media'])
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Tambah satu pada kolom `views` News (Requirement 5.6).
     *
     * Memakai `increment()` agar aman dari race-condition.
     */
    public function incrementViews(News $news): void
    {
        $news->increment('views', 1);
    }

    /**
     * Daftar berita unggulan untuk halaman beranda
     * (Requirement 1.2 — maksimal 6 item).
     *
     * @return iterable<int,News>
     */
    public function latestForHome(int $limit = 6): iterable
    {
        $limit = max(1, min(20, $limit));

        return $this->cache->remember(
            CacheKeys::NEWS_PREFIX.'home:'.$limit,
            now()->addMinutes(5),
            fn () => News::query()
                ->with(['category'])
                ->published()
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
        );
    }
}
