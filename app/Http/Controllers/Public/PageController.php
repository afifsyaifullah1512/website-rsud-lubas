<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller publik untuk halaman custom yang dibuat admin via
 * resource Page. Diakses melalui route `/halaman/{slug}`.
 *
 * Slug halaman bawaan (sejarah, visi-misi, struktur-organisasi,
 * sambutan-direktur, pendaftaran) tetap memiliki URL khusus melalui
 * {@see ProfileController} dan {@see TariffController::registration}.
 * Controller ini menangani slug sembarang lain yang ingin
 * ditambahkan admin sebagai halaman tambahan.
 */
class PageController extends Controller
{
    public function __construct(
        private readonly CacheRepository $cache,
    ) {
    }

    public function show(string $slug): View
    {
        $page = $this->cache->remember(
            CacheKeys::pageSlug($slug),
            now()->addMinutes(5),
            static fn () => Page::query()->where('slug', $slug)->first(),
        );

        if (! $page) {
            throw new NotFoundHttpException();
        }

        $rawBody = (string) $page->body;
        $body = $rawBody;
        try {
            if (class_exists(Purifier::class)) {
                $body = Purifier::clean($rawBody);
            }
        } catch (\Throwable) {
            $body = $rawBody;
        }

        return view('public.page.show', [
            'pageTitle' => $page->title ?: ucfirst(str_replace('-', ' ', $slug)),
            'page' => $page->load('media'),
            'safeBody' => $body,
            'pdfFiles' => $page->media->filter(function ($m) {
                $isPdf = str_starts_with((string) ($m->mime ?? ''), 'application/pdf');
                if (!$isPdf && $m->mime === null) {
                    $isPdf = str_ends_with(strtolower($m->path), '.pdf');
                }
                return $isPdf;
            })->sortBy('sort_order')->values(),
        ]);
    }
}
