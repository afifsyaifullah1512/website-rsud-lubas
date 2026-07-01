<?php

declare(strict_types=1);

use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Support\Enums\PpidCategoryType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5, 32.3, 32.4.
 *
 * Regresi: blade `public.ppid.index` sempat melempar
 * "Object of class App\Support\Enums\PpidCategoryType could not be
 * converted to string" karena enum di-echo langsung. Test ini memastikan
 * halaman publik PPID render tanpa error.
 */
function makePpidDoc(PpidCategoryType $type, array $overrides = []): PpidDocument
{
    $category = PpidCategory::query()->firstOrCreate(
        ['type' => $type->value],
        ['name' => $type->label()],
    );

    return PpidDocument::query()->create(array_merge([
        'category_id' => $category->id,
        'title' => 'Dokumen '.$type->label(),
        'file_path' => 'ppid/'.uniqid().'.pdf',
        'year' => 2024,
        'published_at' => Carbon::now()->subDay(),
    ], $overrides));
}

it('renders the ppid index grouped by category type without enum-to-string error', function () {
    makePpidDoc(PpidCategoryType::BERKALA, ['title' => 'Laporan Tahunan']);

    $this->get('/ppid')
        ->assertOk()
        ->assertSee('Informasi Berkala')
        ->assertSee('Laporan Tahunan');
});

it('renders the ppid type page for a valid slug', function () {
    makePpidDoc(PpidCategoryType::BERKALA, ['title' => 'Laporan Tahunan']);

    $this->get('/ppid/berkala')
        ->assertOk()
        ->assertSee('Laporan Tahunan');
});

it('returns 404 for an unknown type slug', function () {
    $this->get('/ppid/tidak-ada')->assertNotFound();
});

it('hides unpublished documents from the public listing (Req 10.3)', function () {
    makePpidDoc(PpidCategoryType::BERKALA, [
        'title' => 'Dokumen Terjadwal',
        'published_at' => Carbon::now()->addDay(),
    ]);

    $this->get('/ppid')
        ->assertOk()
        ->assertDontSee('Dokumen Terjadwal');
});

it('does not expose a download link for DIKECUALIKAN documents (Req 10.4/32.4)', function () {
    $doc = makePpidDoc(PpidCategoryType::DIKECUALIKAN, ['title' => 'Rahasia Internal']);

    $response = $this->get('/ppid/dikecualikan')->assertOk();
    $response->assertSee('Rahasia Internal');
    $response->assertDontSee(route('ppid.download', $doc->id), false);
});

it('forbids public download of DIKECUALIKAN documents (Req 10.4/32.4)', function () {
    Storage::fake('public');
    $doc = makePpidDoc(PpidCategoryType::DIKECUALIKAN, ['title' => 'Rahasia Internal']);
    Storage::disk('public')->put($doc->file_path, 'isi rahasia');

    $this->get(route('ppid.download', $doc->id))->assertForbidden();
});

it('streams a published non-excluded document for download (Req 10.5)', function () {
    Storage::fake('public');
    $doc = makePpidDoc(PpidCategoryType::BERKALA, ['title' => 'Laporan Tahunan']);
    Storage::disk('public')->put($doc->file_path, 'isi dokumen');

    $this->get(route('ppid.download', $doc->id))->assertOk();
});
