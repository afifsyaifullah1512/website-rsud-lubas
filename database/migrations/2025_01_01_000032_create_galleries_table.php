<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `galleries` — koleksi galeri foto/video RSUD.
 *
 * - `slug` unik dengan format `^[a-z0-9-]+$` (divalidasi di layer
 *   FormRequest sesuai requirements 7.2).
 * - Enum `type` membedakan album foto vs video (requirements 7.1).
 * - Item-item media (foto/video) disimpan di tabel polymorphic
 *   `media` dengan `mediable_type = Gallery::class`.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->enum('type', ['PHOTO', 'VIDEO']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
