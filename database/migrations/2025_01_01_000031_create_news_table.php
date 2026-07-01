<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `news` — berita / pengumuman RSUD.
 *
 * - `category_id` FK ke `news_categories`, dilarang dihapus jika masih
 *   memiliki berita (restrictOnDelete) — requirements 19.1.
 * - `author_id` FK ke `users` (nullable, nullOnDelete) — pengarang
 *   berita; ketika user dihapus, atribut author menjadi NULL agar
 *   data berita tetap historis.
 * - Enum `status` (DRAFT|PUBLISHED|ARCHIVED) — requirements 5.2.
 * - `published_at` tanggal publikasi efektif; visibilitas publik
 *   dibatasi `status = PUBLISHED ∧ published_at <= now()`
 *   (requirements 5.2, P4).
 * - `views` counter unsigned default 0 (incremented saat detail
 *   diakses — requirements 5.3 / Req 5.6).
 * - Indeks komposit `(status, published_at)` mempercepat listing
 *   publik & arsip (requirements 29.2 / 29.5).
 * - Indeks komposit `(category_id, published_at)` mempercepat
 *   listing per kategori.
 * - Soft deletes mendukung P12 (Soft Delete Consistency) —
 *   requirements 33.1 / 33.2.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('news_categories')
                ->restrictOnDelete();
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('title', 200);
            $table->string('slug', 180)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image', 255)->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'published_at'], 'news_status_published_at_idx');
            $table->index(['category_id', 'published_at'], 'news_category_published_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
