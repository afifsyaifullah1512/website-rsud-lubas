<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `doctor_schedules` — jadwal praktik dokter per hari.
 *
 * Validates: Requirements 4.3, 18.1, 18.2, 18.4.
 *
 * - `doctor_id` FK ke `doctors`. Bila Doctor dihapus paksa, jadwalnya
 *   ikut hilang (`cascadeOnDelete`) karena tidak bermakna tanpa dokter.
 * - `polyclinic_id` FK ke `polyclinics`. Memakai `restrictOnDelete`
 *   agar penghapusan keras Polyclinic tidak menghapus history jadwal.
 * - Kolom `day` menggunakan ENUM MySQL untuk konsistensi dengan
 *   `App\Support\Enums\Day` (SENIN..MINGGU).
 * - `start_time` & `end_time` bertipe TIME, validasi `start < end`
 *   ditegakkan di service layer (Requirement 18.2).
 * - Index gabungan `(doctor_id, day, is_active)` untuk mempercepat
 *   pengecekan overlap (lihat Algoritma 2 design.md).
 * - TIDAK menggunakan softDeletes (jadwal aktif/tidak diatur via
 *   `is_active`).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('polyclinic_id')
                ->constrained('polyclinics')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->enum('day', [
                'SENIN',
                'SELASA',
                'RABU',
                'KAMIS',
                'JUMAT',
                'SABTU',
                'MINGGU',
            ]);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('note', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['doctor_id', 'day', 'is_active'],
                'doctor_schedules_doctor_day_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
