<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Hari operasional jadwal dokter.
 *
 * Nilai backing string sengaja sama dengan nama case sehingga
 * persistensi ke kolom enum MySQL "SENIN..MINGGU" konsisten dengan
 * acceptance criteria (Requirement 4.7, 18.2).
 */
enum Day: string
{
    case SENIN = 'SENIN';
    case SELASA = 'SELASA';
    case RABU = 'RABU';
    case KAMIS = 'KAMIS';
    case JUMAT = 'JUMAT';
    case SABTU = 'SABTU';
    case MINGGU = 'MINGGU';

    /**
     * Indeks ordinal hari mulai dari SENIN = 1 hingga MINGGU = 7.
     *
     * Digunakan untuk pengurutan deterministik daftar jadwal
     * (lihat Algoritma 1 pada design.md – `orderBy(day_index)`).
     */
    public function dayIndex(): int
    {
        return match ($this) {
            self::SENIN => 1,
            self::SELASA => 2,
            self::RABU => 3,
            self::KAMIS => 4,
            self::JUMAT => 5,
            self::SABTU => 6,
            self::MINGGU => 7,
        };
    }

    /**
     * Label berbahasa Indonesia (Title Case) untuk tampilan UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::SENIN => 'Senin',
            self::SELASA => 'Selasa',
            self::RABU => 'Rabu',
            self::KAMIS => 'Kamis',
            self::JUMAT => 'Jumat',
            self::SABTU => 'Sabtu',
            self::MINGGU => 'Minggu',
        };
    }

    /**
     * Daftar opsi untuk Filament/Livewire select:
     * key = nilai backing (SENIN..MINGGU), value = label Indonesia.
     *
     * @return array<string,string>
     */
    public static function optionsId(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
