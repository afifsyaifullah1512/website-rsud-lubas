<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `site_settings` (key-value store) untuk pengaturan situs:
 * nama RS, logo, alamat, telp, email, jam operasional, koordinat,
 * sosial media, dll. Lihat requirements 12.1, 26.1.
 *
 * - `key` adalah PRIMARY KEY (string ≤ 100).
 * - `value` disimpan sebagai JSON sehingga dapat menyimpan tipe nilai
 *   beragam (string, url, koordinat lat/lng, list, dll).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
