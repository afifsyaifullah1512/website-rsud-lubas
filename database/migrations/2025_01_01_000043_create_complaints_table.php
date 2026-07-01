<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel `complaints` — pengaduan publik via Public_Site.
 *
 * Mengacu pada requirements:
 * - 11.3: enum status `NEW|IN_REVIEW|RESPONDED|CLOSED`, default `NEW`.
 * - 11.5: setiap complaint memiliki `ticket_number` yang unik.
 * - 11.9: rate limit per IP — index `(ip_address, created_at)`
 *   mempercepat query window 1 jam pada `RateLimiter::tooMany`.
 * - 32.1, 32.2: PII disimpan apa adanya tetapi disanitasi sebelum log.
 *
 * Catatan:
 * - `ticket_number` varchar(32) — format `RSUD-YYYYMMDD-XXXXXX` (21 chars)
 *   dengan margin untuk varian format mendatang.
 * - `ip_address` varchar(45) untuk akomodasi IPv6.
 * - `message` `text` (tidak `longText`) — pengaduan tetap singkat.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 32)->unique();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('phone', 30)->nullable();
            $table->string('subject', 200);
            $table->text('message');
            $table->enum('status', ['NEW', 'IN_REVIEW', 'RESPONDED', 'CLOSED'])->default('NEW');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Rate-limit query: WHERE ip_address=? AND created_at>=?
            $table->index(['ip_address', 'created_at']);
            // Listing admin: ORDER BY status, created_at DESC
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
