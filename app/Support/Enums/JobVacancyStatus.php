<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status entitas Job_Vacancy.
 *
 * Validates: Requirements 22.1.
 */
enum JobVacancyStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Dibuka',
            self::CLOSED => 'Ditutup',
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
