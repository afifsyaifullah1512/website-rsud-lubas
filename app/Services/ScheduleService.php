<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DoctorSchedule;
use App\Support\Enums\Day;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * ScheduleService — domain service untuk operasi jadwal dokter
 * yang membutuhkan validasi lintas baris (mis. deteksi bentrok).
 *
 * Implementasi mengikuti Algoritma 2 di design.md
 * (`checkScheduleOverlap`) dan menjamin properti P1 — Schedule
 * Non-Overlap.
 *
 * Validates: Requirements 18.2, 18.3, 18.4.
 */
final class ScheduleService
{
    /**
     * Periksa apakah interval baru `[start, end)` bentrok dengan
     * jadwal aktif lain milik dokter yang sama pada hari yang sama.
     *
     * Menggunakan formulasi interval setengah-terbuka:
     *
     *     [a, b) ∩ [c, d) ≠ ∅  ⇔  a < d ∧ b > c
     *
     * sehingga jadwal yang saling bersinggungan di batas
     * (mis. 08:00–12:00 dan 12:00–13:00) TIDAK dianggap bentrok.
     *
     * Catatan: kolom `start_time` & `end_time` adalah tipe TIME yang
     * disimpan oleh MySQL sebagai string `H:i:s`. Input `$start` dan
     * `$end` diterima dalam format `H:i` (atau `H:i:s`) — perbandingan
     * di MySQL menggunakan tipe TIME; untuk format `H:i:s` perbandingan
     * leksikografis pun ekuivalen secara numerik.
     *
     * @param  int       $doctorId  ID dokter target
     * @param  Day       $day       Hari jadwal (SENIN..MINGGU)
     * @param  string    $start     Jam mulai (`H:i` / `H:i:s`)
     * @param  string    $end       Jam selesai (`H:i` / `H:i:s`)
     * @param  int|null  $excludeId Jika diisi, jadwal dengan id ini
     *                              dikecualikan (untuk skenario
     *                              update jadwal eksisting).
     *
     * @return bool `true` ⟺ ada minimal satu jadwal aktif lain yang
     *              berpotongan; `false` selainnya.
     *
     * @throws InvalidArgumentException Jika `$start >= $end`.
     */
    public function checkOverlap(
        int $doctorId,
        Day $day,
        string $start,
        string $end,
        ?int $excludeId = null,
    ): bool {
        if ($start >= $end) {
            throw new InvalidArgumentException(
                'start_time harus lebih kecil dari end_time.'
            );
        }

        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day', $day)
            ->where('is_active', true)
            ->when(
                $excludeId !== null,
                fn (Builder $q) => $q->where('id', '!=', $excludeId)
            )
            ->where(function (Builder $q) use ($start, $end): void {
                $q->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->exists();
    }
}
