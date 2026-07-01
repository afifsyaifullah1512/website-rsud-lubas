<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `job_vacancies` untuk pengumuman lowongan kerja RSUD.
 *
 * Mengacu pada requirements:
 * - 9.1: listing lowongan terbuka di Public_Site.
 * - 9.3: setiap lowongan memiliki tanggal pembukaan & penutupan.
 * - 22.1: enum status `OPEN|CLOSED`.
 * - 22.2: invariant `open_at <= close_at` (ditegakkan di layer aplikasi/Form).
 *
 * Catatan:
 * - Slug unique dengan panjang ≤ 180 karakter (selaras pages/news).
 * - Kolom `attachment` menampung path file (PDF) opsional.
 * - Tidak menyimpan FK `posted_by` ke `users` agar migrasi ini bebas
 *   dependensi dari migrasi paralel (tasks 2.4 & 2.5).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 180)->unique();
            $table->longText('description');
            $table->date('open_at');
            $table->date('close_at');
            $table->string('attachment', 255)->nullable();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamps();

            // Index untuk listing publik aktif: WHERE status='OPEN' AND close_at>=today
            $table->index(['status', 'close_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
