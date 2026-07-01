<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `nav_items` — item menu navigasi publik yang dikelola admin
 * via Filament. Mendukung 1 level dropdown (parent + children).
 *
 * - `parent_id` self-FK; root item: parent_id = NULL.
 * - `url` bebas: bisa relatif ('/jadwal-dokter'), full ('https://...'),
 *   atau slug Page custom ('/halaman/visi-misi').
 * - `opens_new_tab` flag untuk target="_blank" (link eksternal).
 * - `sort_order` integer kecil untuk pengurutan.
 * - `is_active` toggle tampilan tanpa hapus row.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('nav_items')
                ->nullOnDelete();
            $table->string('label', 120);
            $table->string('url', 255);
            $table->boolean('opens_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order'], 'nav_items_parent_sort_idx');
            $table->index(['is_active', 'parent_id'], 'nav_items_active_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
