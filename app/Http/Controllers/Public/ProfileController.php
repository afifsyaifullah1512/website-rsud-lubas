<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CacheKeys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Halaman profil RSUD (sejarah, visi-misi, struktur, sambutan direktur).
 *
 * Requirement 2.1–2.4.
 */
class ProfileController extends Controller
{
    public function __construct(
        private readonly CacheRepository $cache,
    ) {
    }

    public function history(): View
    {
        return $this->renderPage('sejarah', 'Sejarah');
    }

    public function visionMission(): View
    {
        return $this->renderPage('visi-misi', 'Visi & Misi');
    }

    public function structure(): View
    {
        return $this->renderPage('struktur-organisasi', 'Struktur Organisasi');
    }

    public function directorMessage(): View
    {
        return $this->renderPage('sambutan-direktur', 'Sambutan Direktur');
    }

    private function renderPage(string $slug, string $fallbackTitle): View
    {
        $page = $this->cache->remember(
            CacheKeys::pageSlug($slug),
            now()->addMinutes(5),
            static fn () => Page::query()->where('slug', $slug)->first(),
        );

        if (! $page) {
            throw new NotFoundHttpException();
        }

        // Sanitize via Purifier kalau tersedia; fallback aman ke konten apa adanya
        // (Blade template tetap auto-escape sehingga {!! !!} dipakai sadar pada
        // konten yang sudah disanitasi di sumbernya).
        $rawBody = (string) $page->body;
        $body = $rawBody;
        try {
            if (class_exists(Purifier::class)) {
                $body = Purifier::clean($rawBody);
            }
        } catch (\Throwable) {
            $body = $rawBody;
        }

        return view('public.profile.show', [
            'pageTitle' => $page->title ?: $fallbackTitle,
            'pageDescription' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? ''), 160),
            'page' => $page,
            'safeBody' => $body,
        ]);
    }
}
