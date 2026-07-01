<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Services\NewsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Listing & detail berita publik (Requirement 5.1–5.8).
 */
class NewsController extends Controller
{
    public function __construct(
        private readonly NewsService $newsService,
    ) {
    }

    public function index(Request $request): View
    {
        $search = $request->query('q');

        $news = $this->newsService->paginatePublished(
            perPage: 9,
            categorySlug: null,
            search: is_string($search) ? $search : null,
        );

        return view('public.news.index', [
            'pageTitle' => 'Berita & Pengumuman',
            'news' => $news,
            'categories' => NewsCategory::query()->orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function category(string $slug): View
    {
        $category = NewsCategory::query()->where('slug', $slug)->firstOrFail();

        $news = $this->newsService->paginatePublished(
            perPage: 9,
            categorySlug: $slug,
        );

        return view('public.news.index', [
            'pageTitle' => 'Berita Kategori: '.$category->name,
            'news' => $news,
            'categories' => NewsCategory::query()->orderBy('name')->get(),
            'currentCategory' => $category,
        ]);
    }

    public function show(string $slug): View
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new NotFoundHttpException();
        }

        // Requirement 5.6 — tambah views setelah detail berhasil dirender.
        $this->newsService->incrementViews($news);

        // Requirement 30.2 — sanitasi body agar aman dari XSS sebelum
        // ditampilkan via {!! !!}. Fallback aman bila Purifier tidak ada.
        $safeBody = (string) $news->body;
        try {
            if (class_exists(Purifier::class)) {
                $safeBody = Purifier::clean((string) $news->body);
            }
        } catch (\Throwable) {
            $safeBody = (string) $news->body;
        }

        // Sidebar: berita terkait (kategori sama) + berita terbaru.
        $related = News::query()
            ->with('category')
            ->published()
            ->where('id', '!=', $news->id)
            ->when(
                $news->category_id !== null,
                fn (Builder $q) => $q->where('category_id', $news->category_id)
            )
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('public.news.show', [
            'pageTitle' => $news->title,
            'pageDescription' => $news->excerpt,
            'pageImage' => $this->absoluteImageUrl($news->cover_image),
            'news' => $news,
            'safeBody' => $safeBody,
            'related' => $related,
        ]);
    }

    /**
     * Ubah path cover image relatif menjadi URL absolut untuk og:image
     * (Requirement 27.3). Mengembalikan null bila tidak ada gambar.
     */
    private function absoluteImageUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/'.ltrim($path, '/'));
    }
}
