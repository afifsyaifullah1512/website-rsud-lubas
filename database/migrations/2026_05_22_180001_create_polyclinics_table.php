<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `polyclinics` (poliklinik) sebagai unit pelayanan medis.
 *
 * Validates: Requirements 17.1, 17.4, 33.1.
 *
 * - `slug` unique dan akan divalidasi pada layer aplikasi terhadap
 *   regex `^[a-z0-9-]+$` (tidak dipaksakan di MySQL agar portabel).
 * - `is_active` mengatur visibilitas publik (Requirement 1.4, 4.3).
 * - `sort_order` digunakan untuk urutan tampilan navigasi/listing.
 * - Soft delete diaktifkan agar entitas yang dihapus tidak hilang dari
 *   data historis (Requirement 33.1, 33.2).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('polyclinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polyclinics');
    }
};
