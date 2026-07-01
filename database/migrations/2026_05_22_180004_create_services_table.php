<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `services` — layanan rumah sakit (Poli, Rawat Inap, IGD,
 * Penunjang, Unggulan).
 *
 * Validates: Requirements 3.4, 17.1, 17.2, 17.4, 33.1.
 *
 * - `slug` unique untuk URL `/layanan/{slug}`.
 * - `type` ENUM dengan nilai sesuai `App\Support\Enums\ServiceType`.
 * - `polyclinic_id` nullable: tidak semua layanan terikat poliklinik
 *   (mis. IGD, Penunjang). Strategi `nullOnDelete` agar penghapusan
 *   Polyclinic tidak menghapus Service, melainkan melepaskan tautan.
 * - Soft delete agar listing publik dapat memfilter `deleted_at IS NULL`
 *   (Requirement 3.4, 33.1, 33.2).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polyclinic_id')
                ->nullable()
                ->constrained('polyclinics')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->enum('type', [
                'POLI',
                'RAWAT_INAP',
                'IGD',
                'PENUNJANG',
                'UNGGULAN',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
