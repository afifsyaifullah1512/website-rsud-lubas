<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `pages` untuk halaman statis berbasis slug
 * (sejarah, visi-misi, struktur-organisasi, sambutan-direktur, dll).
 *
 * Sesuai requirements 2.2 (slug Page) dan 16.2 (slug regex `^[a-z0-9-]+$`
 * dan unique di tabel `pages`).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 200);
            $table->longText('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
