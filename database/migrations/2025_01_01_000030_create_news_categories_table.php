<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `news_categories` — kategori berita/pengumuman.
 *
 * Setiap News berasal dari satu kategori (FK `category_id`).
 * Slug unik dan harus mengikuti regex `^[a-z0-9-]+$` (validasi di
 * layer aplikasi / FormRequest, lihat requirements 19.5).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_categories');
    }
};
