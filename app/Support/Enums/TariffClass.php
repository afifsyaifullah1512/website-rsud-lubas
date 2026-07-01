<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Kelas tarif yang sah pada entitas Tariff.
 *
 * Validates: Requirements 19.1.
 */
enum TariffClass: string
{
    case VIP = 'VIP';
    case KELAS_1 = 'KELAS_1';
    case KELAS_2 = 'KELAS_2';
    case KELAS_3 = 'KELAS_3';
    case EKSEKUTIF = 'EKSEKUTIF';
    case UMUM = 'UMUM';

    public function label(): string
    {
        return match ($this) {
            self::VIP => 'VIP',
            self::KELAS_1 => 'Kelas 1',
            self::KELAS_2 => 'Kelas 2',
            self::KELAS_3 => 'Kelas 3',
            self::EKSEKUTIF => 'Eksekutif',
            self::UMUM => 'Umum',
        };
    }

    /**
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
