<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `complaint_logs` — riwayat transisi status Complaint.
 *
 * Mengacu pada requirements:
 * - 11.3: setiap perubahan status dicatat (timeline pengecekan publik).
 * - 24.2, 24.3: audit perubahan status di admin (siapa, kapan, ke status apa).
 *
 * Catatan:
 * - `complaint_id` FK -> `complaints` dengan `cascadeOnDelete` agar
 *   penghapusan Complaint membersihkan riwayatnya.
 * - `user_id` nullable FK -> `users` dengan `nullOnDelete` (entri awal
 *   submit publik tidak punya user; user yang dihapus tetap menyisakan log).
 * - `note` `text` nullable — catatan opsional saat transisi status.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('complaint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')
                ->constrained('complaints')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('status', ['NEW', 'IN_REVIEW', 'RESPONDED', 'CLOSED']);
            $table->text('note')->nullable();
            $table->timestamps();

            // Timeline per complaint: ORDER BY created_at
            $table->index(['complaint_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_logs');
    }
};
