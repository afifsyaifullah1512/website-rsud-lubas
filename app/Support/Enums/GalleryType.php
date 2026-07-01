<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Tipe konten Gallery (foto atau video).
 *
 * Validates: Requirements 7.1, 20.1.
 */
enum GalleryType: string
{
    case PHOTO = 'PHOTO';
    case VIDEO = 'VIDEO';

    public function label(): string
    {
        return match ($this) {
            self::PHOTO => 'Foto',
            self::VIDEO => 'Video',
        };
    }

    /**
     * Daftar opsi untuk Filament/Livewire select:
     * key = nilai backing (PHOTO/VIDEO), value = label Indonesia.
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
