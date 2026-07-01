<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `doctors` (dokter) yang melekat pada satu Polyclinic.
 *
 * Validates: Requirements 4.3, 18.1, 33.1.
 *
 * - `polyclinic_id` adalah FK wajib ke `polyclinics.id`. Strategi delete
 *   adalah `restrictOnDelete` karena penghapusan keras Polyclinic
 *   harus eksplisit (di praktiknya entitas akan disoft-delete).
 * - `slug` unique untuk URL `/dokter/{slug}` (Requirement 4.5).
 * - `is_active` mengatur tampil/tidak di publik.
 * - Soft delete agar konsisten dengan invariant P12 (lihat design.md).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polyclinic_id')
                ->constrained('polyclinics')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->string('photo')->nullable();
            $table->string('specialization', 120);
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
