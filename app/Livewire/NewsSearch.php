<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Pencarian berita publik secara live tanpa memuat ulang halaman
 * (Requirement 5.8).
 *
 * Visibility hanya untuk News `status = PUBLISHED ∧ published_at <= now()`
 * (P4, Requirement 5.2) melalui scope `published()`. Pencarian aktif hanya
 * ketika kata kunci minimal {@see self::MIN_CHARS} karakter.
 */
class NewsSearch extends Component
{
    /** Panjang minimum kata kunci agar pencarian dijalankan. */
    public const MIN_CHARS = 2;

    /** Batas maksimum hasil yang ditampilkan pada panel live. */
    public const MAX_RESULTS = 8;

    /**
     * Kata kunci pencarian. Disinkronkan ke query string `?q=` agar
     * hasil dapat di-bookmark dan kompatibel dengan listing server-side.
     */
    #[Url(as: 'q', except: '')]
    public string $q = '';

    /**
     * Bangun hasil pencarian + status, lalu render.
     */
    public function render(): View
    {
        $term = trim($this->q);
        $isSearching = mb_strlen($term) >= self::MIN_CHARS;

        $results = collect();
        if ($isSearching) {
            $like = '%'.$term.'%';
            $results = News::query()
                ->with('category')
                ->published()
                ->where(function (Builder $w) use ($like): void {
                    $w->where('title', 'LIKE', $like)
                        ->orWhere('excerpt', 'LIKE', $like);
                })
                ->orderByDesc('published_at')
                ->limit(self::MAX_RESULTS)
                ->get();
        }

        return view('livewire.news-search', [
            'results' => $results,
            'isSearching' => $isSearching,
        ]);
    }
}
