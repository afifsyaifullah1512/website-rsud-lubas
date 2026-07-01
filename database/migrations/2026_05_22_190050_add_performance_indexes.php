<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi performa: tambah composite index yang belum dibuat saat
 * migrasi pembuatan tabel awal.
 *
 * Validates: Requirements 29.2, 29.5.
 *
 * Audit terhadap design.md §"Database Index" pada saat task 10.1
 * dijalankan menemukan:
 *
 * - `news(status, published_at)`            → sudah ada (migrasi 2.5).
 * - `news(category_id, published_at)`       → sudah ada (migrasi 2.5).
 * - `doctor_schedules(doctor_id, day, is_active)` → sudah ada (migrasi 2.4).
 * - Unique pada semua `slug` & `complaints.ticket_number` → sudah ada
 *   (migrasi 2.3 / 2.4 / 2.5 / 2.6).
 * - `doctors(polyclinic_id, is_active)`     → BELUM ADA. Hanya tersedia
 *   index FK tunggal `doctors_polyclinic_id_foreign` pada
 *   `polyclinic_id`, sehingga query listing publik
 *   `WHERE polyclinic_id=? AND is_active=1 AND deleted_at IS NULL`
 *   (lihat algoritma listing dokter pada design.md §Doctor) tidak
 *   tercakup oleh index komposit. Dibuat di sini.
 *
 * Catatan implementasi:
 * - Index FK eksisting `doctors_polyclinic_id_foreign` SENGAJA tidak
 *   dihapus. MySQL membutuhkan index pada kolom FK; menghapusnya
 *   memerlukan drop & recreate constraint dan tidak memberi
 *   keuntungan signifikan dibanding biaya migrasi.
 * - Nama index eksplisit `doctors_polyclinic_active_idx` agar
 *   `down()` dapat melakukan drop deterministik.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->index(
                ['polyclinic_id', 'is_active'],
                'doctors_polyclinic_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('doctors_polyclinic_active_idx');
        });
    }
};
