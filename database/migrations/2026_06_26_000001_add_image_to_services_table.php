<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom gambar opsional untuk Service (dipakai kartu Layanan Unggulan
 * bergambar di beranda). Aditif — tidak mengubah kolom lain.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('image')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('image');
        });
    }
};
