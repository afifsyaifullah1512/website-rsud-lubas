<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NavItem;
use App\Support\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Membangun tree menu navigasi publik dari tabel `nav_items`.
 *
 * Hasil di-cache forever; di-invalidate oleh {@see \App\Observers\NavItemObserver}
 * saat ada perubahan menu.
 *
 * Struktur tree:
 *   Collection<int, array{
 *       id: int,
 *       label: string,
 *       url: string,
 *       opens_new_tab: bool,
 *       children: array<int, array{
 *           id: int,
 *           label: string,
 *           url: string,
 *           opens_new_tab: bool,
 *       }>,
 *   }>
 */
final class NavMenuService
{
    /**
     * @return Collection<int,array<string,mixed>>
     */
    public function tree(): Collection
    {
        /** @var Collection<int,array<string,mixed>> $tree */
        $tree = Cache::rememberForever(CacheKeys::NAV_MENU, function (): Collection {
            $roots = NavItem::query()
                ->root()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->with(['children' => fn ($q) => $q->where('is_active', true)])
                ->get();

            return $roots->map(static function (NavItem $item): array {
                return [
                    'id' => (int) $item->id,
                    'label' => (string) $item->label,
                    'url' => (string) $item->url,
                    'opens_new_tab' => (bool) $item->opens_new_tab,
                    'children' => $item->children->map(static fn (NavItem $c) => [
                        'id' => (int) $c->id,
                        'label' => (string) $c->label,
                        'url' => (string) $c->url,
                        'opens_new_tab' => (bool) $c->opens_new_tab,
                    ])->all(),
                ];
            });
        });

        return $tree;
    }
}
