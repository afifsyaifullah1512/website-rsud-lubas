<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status alur penanganan Complaint.
 *
 * Transisi sah: NEW → IN_REVIEW → RESPONDED → CLOSED
 * (super-admin boleh memindahkan status apa pun ke CLOSED).
 *
 * Validates: Requirements 11.3, 24.3.
 */
enum ComplaintStatus: string
{
    case NEW = 'NEW';
    case IN_REVIEW = 'IN_REVIEW';
    case RESPONDED = 'RESPONDED';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::IN_REVIEW => 'Sedang Ditinjau',
            self::RESPONDED => 'Sudah Direspons',
            self::CLOSED => 'Selesai',
        };
    }

    /**
     * Daftar transisi normal yang diizinkan dari status saat ini.
     *
     * @return array<int,self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::NEW => [self::IN_REVIEW, self::CLOSED],
            self::IN_REVIEW => [self::RESPONDED, self::CLOSED],
            self::RESPONDED => [self::CLOSED],
            self::CLOSED => [],
        };
    }

    /**
     * Apakah transisi ke status `$next` diperbolehkan oleh aturan normal
     * (tanpa hak istimewa super-admin).
     */
    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNext(), true);
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
