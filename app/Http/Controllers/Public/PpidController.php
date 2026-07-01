<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Support\Enums\PpidCategoryType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman PPID publik (Requirement 10.1–10.5, 32.3, 32.4).
 *
 * Catatan privasi: dokumen kategori `DIKECUALIKAN` HANYA menampilkan
 * metadata dan dasar pengecualian — tanpa tautan unduh langsung
 * (Req 10.4, 32.4). Hanya dokumen `published_at <= now()` yang tampil
 * (Req 10.3 via scope `published`).
 */
class PpidController extends Controller
{
    /**
     * Daftar dokumen PPID dikelompokkan berdasarkan kategori/tipe
     * (Req 10.1). Setiap tipe ditampilkan berurutan sesuai enum.
     */
    public function index(): View
    {
        $categories = PpidCategory::query()
            ->with(['documents' => fn ($q) => $q->published()
                ->orderByDesc('year')
                ->orderByDesc('published_at')])
            ->orderBy('name')
            ->get();

        return view('public.ppid.index', [
            'pageTitle' => 'Pejabat Pengelola Informasi & Dokumentasi (PPID)',
            'pageDescription' => 'Transparansi informasi publik RSUD sesuai UU KIP No. 14/2008: informasi berkala, serta merta, setiap saat, dan dikecualikan.',
            'groups' => $this->groupByType($categories),
        ]);
    }

    /**
     * Dokumen untuk satu tipe kategori (Req 10.2).
     * Slug tidak valid menghasilkan 404.
     */
    public function byType(string $type): View
    {
        $typeEnum = $this->resolveType($type);

        $categories = PpidCategory::query()
            ->with(['documents' => fn ($q) => $q->published()
                ->orderByDesc('year')
                ->orderByDesc('published_at')])
            ->where('type', $typeEnum->value)
            ->orderBy('name')
            ->get();

        return view('public.ppid.type', [
            'pageTitle' => 'PPID — '.$typeEnum->label(),
            'pageDescription' => 'Dokumen PPID kategori '.$typeEnum->label().' RSUD.',
            'type' => $typeEnum,
            'categories' => $categories,
            'isExcluded' => $typeEnum === PpidCategoryType::DIKECUALIKAN,
        ]);
    }

    /**
     * Stream/unduh dokumen PPID dari disk (Req 10.5).
     *
     * - Hanya dokumen terpublikasi (`published_at <= now()`).
     * - Kategori `DIKECUALIKAN` tidak boleh diunduh publik → 403 (Req 10.4, 32.4).
     */
    public function download(int $id): StreamedResponse
    {
        $doc = PpidDocument::query()
            ->with('category')
            ->published()
            ->whereKey($id)
            ->first();

        if (! $doc) {
            abort(404);
        }

        // Dokumen DIKECUALIKAN tidak menyediakan tautan unduh publik.
        if ($doc->category?->type === PpidCategoryType::DIKECUALIKAN) {
            abort(403);
        }

        $disk = Storage::disk('public');

        if (! $doc->file_path || ! $disk->exists($doc->file_path)) {
            abort(404);
        }

        return $disk->download($doc->file_path);
    }

    /**
     * Kelompokkan kategori per tipe sesuai urutan enum, sehingga
     * setiap tipe selalu memiliki seksi (meski kosong).
     *
     * @param  Collection<int,PpidCategory>  $categories
     * @return Collection<int,array{type:PpidCategoryType,categories:Collection<int,PpidCategory>}>
     */
    private function groupByType(Collection $categories): Collection
    {
        return collect(PpidCategoryType::cases())->map(fn (PpidCategoryType $type) => [
            'type' => $type,
            'categories' => $categories
                ->filter(fn (PpidCategory $c) => $c->type === $type)
                ->values(),
        ]);
    }

    /**
     * Petakan slug URL ke enum tipe kategori; 404 bila tidak dikenal.
     */
    private function resolveType(string $slug): PpidCategoryType
    {
        return match (strtolower($slug)) {
            'berkala' => PpidCategoryType::BERKALA,
            'serta-merta' => PpidCategoryType::SERTA_MERTA,
            'setiap-saat' => PpidCategoryType::SETIAP_SAAT,
            'dikecualikan' => PpidCategoryType::DIKECUALIKAN,
            default => abort(404),
        };
    }
}
