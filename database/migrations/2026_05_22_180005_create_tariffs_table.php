<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `tariffs` — item tarif yang melekat pada Service.
 *
 * Validates: Requirements 8.4, 21.1, 21.2.
 *
 * - `service_id` FK ke `services`. Bila Service dihapus keras, item
 *   tarifnya ikut terhapus karena tidak punya konteks lagi.
 * - `price` DECIMAL(12,2) UNSIGNED untuk menjamin invariant `price >= 0`
 *   (Requirement 8.4, 21.2 / property P6) di tingkat skema MySQL.
 * - `class` nullable ENUM sesuai `App\Support\Enums\TariffClass`.
 * - TIDAK menggunakan softDeletes (perubahan tarif dikelola via update
 *   atau hapus permanen oleh admin keuangan).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('item_name', 200);
            $table->decimal('price', 12, 2)->unsigned();
            $table->enum('class', [
                'VIP',
                'KELAS_1',
                'KELAS_2',
                'KELAS_3',
                'EKSEKUTIF',
                'UMUM',
            ])->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
