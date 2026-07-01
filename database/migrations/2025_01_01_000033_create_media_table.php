<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `media` — penyimpanan polymorphic untuk berkas yang
 * melekat pada entitas seperti Gallery, News, Page, JobVacancy,
 * PpidDocument, dsb (lihat ERD pada design.md).
 *
 * - `morphs('mediable')` menambah `mediable_type` (string) dan
 *   `mediable_id` (unsignedBigInteger) plus indeks komposit
 *   bawaan `(mediable_type, mediable_id)` untuk lookup cepat.
 * - `disk` (≤60) menyimpan nama disk Laravel (`public`, `local`,
 *   `s3`, dst) sesuai kebijakan privat/publik (lihat 32.3 untuk
 *   PPID kategori DIKECUALIKAN).
 * - `path` (≤500) — path relatif pada disk.
 * - `mime` (≤120) — tipe MIME, divalidasi di FormRequest.
 * - `size` — ukuran berkas dalam bytes.
 * - `caption` (≤255) — keterangan opsional.
 * - `sort_order` — urutan tampilan; indeks tambahan
 *   `(mediable_type, mediable_id, sort_order)` mempercepat
 *   pengambilan media terurut per parent (requirements 20.2).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('disk', 60);
            $table->string('path', 500);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(
                ['mediable_type', 'mediable_id', 'sort_order'],
                'media_mediable_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
