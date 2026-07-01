<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `ppid_categories` — klasifikasi informasi PPID
 * sesuai UU 14/2008 KIP.
 *
 * Mengacu pada requirements:
 * - 10.1: listing dokumen PPID dikelompokkan per kategori.
 * - 23.1: enum `type` ∈ {BERKALA, SERTA_MERTA, SETIAP_SAAT, DIKECUALIKAN}.
 *
 * Catatan:
 * - Tidak menyimpan slug — kategori dirujuk oleh enum `type` di URL
 *   (contoh: `/ppid/BERKALA`).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ppid_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->enum('type', ['BERKALA', 'SERTA_MERTA', 'SETIAP_SAAT', 'DIKECUALIKAN']);
            $table->timestamps();

            // Filter publik: WHERE type=?
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_categories');
    }
};
