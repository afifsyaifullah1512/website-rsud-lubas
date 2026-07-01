<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Galeri publik (Requirement 7.1–7.3).
 */
class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::query()
            ->with(['media' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('title')
            ->get()
            ->groupBy(
                fn (Gallery $g) => $g->type instanceof \App\Support\Enums\GalleryType
                ? $g->type->value
                : (string) $g->type
            );

        return view('public.gallery.index', [
            'pageTitle' => 'Galeri',
            'galleries' => $galleries,
        ]);
    }

    public function show(string $slug): View
    {
        $gallery = Gallery::query()
            ->with(['media' => fn ($q) => $q->orderBy('sort_order')])
            ->where('slug', $slug)
            ->first();

        if (! $gallery) {
            throw new NotFoundHttpException();
        }

        return view('public.gallery.show', [
            'pageTitle' => $gallery->title,
            'gallery' => $gallery,
        ]);
    }
}
