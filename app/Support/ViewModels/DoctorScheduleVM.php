<?php

declare(strict_types=1);

namespace App\Support\ViewModels;

use App\Models\DoctorSchedule;
use App\Support\Enums\Day;

/**
 * View model imutabel untuk satu baris jadwal dokter pada halaman
 * publik `/jadwal-dokter`.
 *
 * Tujuan VM ini:
 *  - Menjaga Blade template terisolasi dari struktur Eloquent
 *    (mis. relasi lazy-load) sehingga query tambahan tidak terjadi
 *    saat rendering — Requirement 29.2.
 *  - Memberi struktur yang mudah dipakai oleh komponen Livewire
 *    `DoctorScheduleFilter` dan endpoint detail `/dokter/{slug}`
 *    (Requirement 4.1, 4.5).
 *  - Mendukung serialisasi cache (Requirement 4.8, 29.5).
 *
 * Catatan: nilai `start` dan `end` disimpan dalam format `H:i`
 * (5 karakter) sehingga aman dipakai di Blade tanpa parsing tambahan.
 */
final readonly class DoctorScheduleVM
{
    public function __construct(
        public int $id,
        public int $doctorId,
        public string $doctorName,
        public string $doctorSlug,
        public ?string $doctorPhotoUrl,
        public string $doctorSpecialization,
        public int $polyclinicId,
        public string $polyclinicName,
        public string $polyclinicSlug,
        public Day $day,
        public string $startTime,
        public string $endTime,
        public ?string $note,
    ) {
    }

    /**
     * Bangun VM dari instance Eloquent {@see DoctorSchedule}
     * yang sudah memuat relasi `doctor.polyclinic` (dieksekusi
     * di repository). Memanggil method ini tanpa eager loading
     * tetap aman tetapi akan menimbulkan query tambahan
     * (lihat Requirement 29.2 untuk anti-N+1).
     */
    public static function fromModel(DoctorSchedule $schedule): self
    {
        $doctor = $schedule->doctor;
        $polyclinic = $schedule->polyclinic ?? $doctor?->polyclinic;

        return new self(
            id: (int) $schedule->id,
            doctorId: (int) ($doctor?->id ?? $schedule->doctor_id),
            doctorName: (string) ($doctor?->name ?? ''),
            doctorSlug: (string) ($doctor?->slug ?? ''),
            doctorPhotoUrl: self::photoUrl($doctor?->photo),
            doctorSpecialization: (string) ($doctor?->specialization ?? ''),
            polyclinicId: (int) ($polyclinic?->id ?? $schedule->polyclinic_id),
            polyclinicName: (string) ($polyclinic?->name ?? ''),
            polyclinicSlug: (string) ($polyclinic?->slug ?? ''),
            day: $schedule->day instanceof Day
                ? $schedule->day
                : Day::from((string) $schedule->day),
            startTime: self::trimTime((string) $schedule->start_time),
            endTime: self::trimTime((string) $schedule->end_time),
            note: $schedule->note !== null ? (string) $schedule->note : null,
        );
    }

    /**
     * Format ulang nilai TIME (`H:i:s` atau `H:i`) menjadi `H:i`
     * agar tampilan publik konsisten.
     */
    private static function trimTime(string $value): string
    {
        if ($value === '') {
            return '';
        }

        // Hapus detik bila ada (mis. `08:00:00` → `08:00`).
        if (strlen($value) >= 5 && substr_count($value, ':') === 2) {
            return substr($value, 0, 5);
        }

        return $value;
    }

    /**
     * Bangun URL aset publik untuk foto dokter. Nilai `null`
     * dipertahankan agar Blade dapat memilih placeholder.
     */
    private static function photoUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }
}
