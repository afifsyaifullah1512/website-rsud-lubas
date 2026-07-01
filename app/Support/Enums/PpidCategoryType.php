<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Klasifikasi kategori PPID berdasarkan UU KIP.
 *
 * Validates: Requirements 23.1, 32.3.
 */
enum PpidCategoryType: string
{
    case BERKALA = 'BERKALA';
    case SERTA_MERTA = 'SERTA_MERTA';
    case SETIAP_SAAT = 'SETIAP_SAAT';
    case DIKECUALIKAN = 'DIKECUALIKAN';

    public function label(): string
    {
        return match ($this) {
            self::BERKALA => 'Informasi Berkala',
            self::SERTA_MERTA => 'Informasi Serta Merta',
            self::SETIAP_SAAT => 'Informasi Setiap Saat',
            self::DIKECUALIKAN => 'Informasi Dikecualikan',
        };
    }

    /**
     * Slug URL untuk rute publik `ppid.type`.
     */
    public function slug(): string
    {
        return match ($this) {
            self::BERKALA => 'berkala',
            self::SERTA_MERTA => 'serta-merta',
            self::SETIAP_SAAT => 'setiap-saat',
            self::DIKECUALIKAN => 'dikecualikan',
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
