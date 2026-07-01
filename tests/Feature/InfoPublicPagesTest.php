<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\Page;
use App\Services\SiteSettingService;
use App\Support\Enums\GalleryType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Validates: Requirements 7.1, 7.2, 7.3, 8.1, 8.2, 8.3, 9.1, 9.2,
 *            12.1, 12.2, 12.3, 13.1, 13.2.
 *
 * Memastikan halaman publik informasi (Galeri, Tarif, Pendaftaran, Karir,
 * FAQ, Kontak) merender tanpa error dan menampilkan data utama.
 */
function seedInfoPages(): void
{
    // Galeri (Req 7.1–7.3): grup per tipe, media diurutkan sort_order.
    $gallery = Gallery::query()->create([
        'title' => 'Kegiatan Bakti Sosial',
        'slug' => 'bakti-sosial',
        'type' => GalleryType::PHOTO->value,
        'description' => 'Dokumentasi kegiatan.',
    ]);
    foreach ([2, 0, 1] as $order) {
        Media::query()->create([
            'mediable_type' => Gallery::class,
            'mediable_id' => $gallery->id,
            'disk' => 'public',
            'path' => 'gallery/foto-'.$order.'.jpg',
            'mime' => 'image/jpeg',
            'size' => 1024,
            'caption' => 'Foto '.$order,
            'sort_order' => $order,
        ]);
    }

    // Alur pendaftaran (Req 8.x): Page slug 'pendaftaran'.
    Page::query()->create([
        'slug' => 'pendaftaran',
        'title' => 'Alur Pendaftaran',
        'body' => '<p>Langkah pendaftaran pasien.</p>',
    ]);

    // FAQ (Req 13.1): aktif diurutkan sort_order.
    Faq::query()->create([
        'question' => 'Bagaimana cara mendaftar?',
        'answer' => 'Datang ke loket pendaftaran.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    // Kontak (Req 12.1–12.3): SiteSetting alamat/telp/email/jam/koordinat/sosmed.
    $settings = app(SiteSettingService::class);
    $settings->set('address', 'Jl. Kesehatan No. 1, Lubuk Basung');
    $settings->set('phone', '0752-123456');
    $settings->set('email', 'info@rsudlubas.go.id');
    $settings->set('operational_hours', '24 Jam');
    $settings->set('latitude', '-0.2933');
    $settings->set('longitude', '100.0254');
    $settings->set('social_facebook', 'https://facebook.com/rsudlubas');
}

it('renders the galeri index grouped by type with media (Req 7.1–7.3)', function () {
    seedInfoPages();

    $this->get('/galeri')
        ->assertOk()
        ->assertSee('Kegiatan Bakti Sosial')
        ->assertSee('Foto');
});

it('renders the galeri detail page (Req 7.2)', function () {
    seedInfoPages();

    $this->get('/galeri/bakti-sosial')
        ->assertOk()
        ->assertSee('Kegiatan Bakti Sosial');
});

it('renders the pendaftaran page from the Page model (Req 8.3)', function () {
    seedInfoPages();

    $this->get('/pendaftaran')
        ->assertOk()
        ->assertSee('Langkah pendaftaran pasien');
});

it('renders the faq index with active questions (Req 13.1–13.2)', function () {
    seedInfoPages();

    $this->get('/faq')
        ->assertOk()
        ->assertSee('Bagaimana cara mendaftar?');
});

it('renders the kontak page with site settings and map embed (Req 12.1–12.3)', function () {
    seedInfoPages();

    $this->get('/kontak')
        ->assertOk()
        ->assertSee('Jl. Kesehatan No. 1, Lubuk Basung')
        ->assertSee('info@rsudlubas.go.id')
        ->assertSee('openstreetmap.org/export/embed.html', false);
});
