<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `faqs` — pertanyaan & jawaban yang sering diajukan
 * (requirements 13.1).
 *
 * - `question` (≤255) — judul pertanyaan.
 * - `answer` text — jawaban (di-sanitasi sebelum disimpan jika
 *   mengandung HTML kaya).
 * - `sort_order` — urutan tampilan accordion publik.
 * - `is_active` — toggle visibilitas; hanya FAQ aktif yang
 *   ditampilkan di Public_Site.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 255);
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
