<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `hero_slides` — slide pada hero slider (carousel) beranda
 * yang dikelola admin via Filament (requirements 35.2, 36.2, 36.7).
 *
 * Tabel mandiri (bukan memanfaatkan `media` polymorphic) karena tiap
 * slide membawa field teks/CTA terstruktur sendiri.
 *
 * - `image_path` — path gambar pada public disk (wajib).
 * - `headline` / `subheadline` — teks opsional di atas slide.
 * - `cta_label` / `cta_url` — pasangan tombol call-to-action opsional;
 *   harus terisi berpasangan (divalidasi di layer Resource/FormRequest).
 * - `sort_order` — urutan tampilan ascending pada carousel.
 * - `is_active` — toggle visibilitas; hanya slide aktif yang dirender.
 *
 * Murni aditif: hanya membuat tabel baru, tidak mengubah migrasi inti.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image_path', 500);
            $table->string('headline', 150)->nullable();
            $table->string('subheadline', 255)->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'hero_slides_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
