<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status publikasi entitas News.
 *
 * Validates: Requirements 5.2.
 */
enum NewsStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Dipublikasikan',
            self::ARCHIVED => 'Diarsipkan',
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
