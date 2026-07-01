<?php

declare(strict_types=1);

use App\Livewire\NewsSearch;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\Enums\NewsStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Validates: Requirements 5.8 (pencarian berita live ≥ 2 karakter pada
 * title/excerpt) dan 5.2 (visibilitas hanya PUBLISHED ∧ published_at <= now()).
 */

function makeNews(array $overrides = []): News
{
    $category = NewsCategory::query()->firstOrCreate(
        ['slug' => 'pengumuman'],
        ['name' => 'Pengumuman'],
    );

    return News::query()->create(array_merge([
        'category_id' => $category->id,
        'title' => 'Berita Default',
        'slug' => 'berita-'.uniqid(),
        'excerpt' => 'Ringkasan default',
        'body' => str_repeat('Isi berita yang cukup panjang. ', 5),
        'status' => NewsStatus::PUBLISHED,
        'published_at' => Carbon::now()->subDay(),
    ], $overrides));
}

it('returns no results when query is shorter than the minimum length', function () {
    makeNews(['title' => 'Vaksinasi Massal']);

    Livewire::test(NewsSearch::class)
        ->set('q', 'v')
        ->assertSet('q', 'v')
        ->assertViewHas('isSearching', false)
        ->assertViewHas('results', fn ($results) => $results->isEmpty());
});

it('matches published news by title for queries of at least two characters', function () {
    $match = makeNews(['title' => 'Vaksinasi Massal COVID-19']);
    makeNews(['title' => 'Donor Darah Rutin']);

    Livewire::test(NewsSearch::class)
        ->set('q', 'vaksinasi')
        ->assertViewHas('isSearching', true)
        ->assertViewHas('results', fn ($results) => $results->pluck('id')->all() === [$match->id]);
});

it('matches published news by excerpt', function () {
    $match = makeNews(['title' => 'Layanan Baru', 'excerpt' => 'Poliklinik jantung kini tersedia']);
    makeNews(['title' => 'Lainnya', 'excerpt' => 'Tidak relevan']);

    Livewire::test(NewsSearch::class)
        ->set('q', 'jantung')
        ->assertViewHas('results', fn ($results) => $results->pluck('id')->all() === [$match->id]);
});

it('excludes draft and future-dated news from results', function () {
    makeNews(['title' => 'Imunisasi Draft', 'status' => NewsStatus::DRAFT]);
    makeNews(['title' => 'Imunisasi Terjadwal', 'published_at' => Carbon::now()->addDay()]);

    Livewire::test(NewsSearch::class)
        ->set('q', 'imunisasi')
        ->assertViewHas('results', fn ($results) => $results->isEmpty());
});

it('renders matching result title in the dropdown HTML', function () {
    makeNews(['title' => 'Operasi Katarak Gratis']);

    Livewire::test(NewsSearch::class)
        ->set('q', 'katarak')
        ->assertSee('Operasi Katarak Gratis');
});

it('shows empty-state message when nothing matches', function () {
    makeNews(['title' => 'Berita Apa Saja']);

    Livewire::test(NewsSearch::class)
        ->set('q', 'zzzztidakada')
        ->assertSee('Tidak ada berita yang cocok');
});
