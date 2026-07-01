<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `ppid_documents` — dokumen PPID per kategori.
 *
 * Mengacu pada requirements:
 * - 10.1, 10.3: listing/akses dokumen PPID di Public_Site.
 * - 23.1: dokumen tergolong ke salah satu PpidCategoryType.
 *
 * Catatan:
 * - `category_id` FK -> `ppid_categories` dengan `restrictOnDelete`
 *   agar kategori yang masih memiliki dokumen tidak dapat dihapus.
 * - `file_path` panjang 500 karakter untuk akomodasi path disk.
 * - `published_at` nullable: hanya tampil publik bila `<= now()`.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ppid_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('ppid_categories')
                ->restrictOnDelete();
            $table->string('title', 200);
            $table->string('file_path', 500);
            $table->unsignedSmallInteger('year');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Listing per kategori, urut tahun & publikasi
            $table->index(['category_id', 'year']);
            $table->index(['category_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_documents');
    }
};
