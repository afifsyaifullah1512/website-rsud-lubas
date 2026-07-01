<?php

declare(strict_types=1);

namespace App\Support\ValueObjects;

use App\Support\Enums\Day;

/**
 * DTO immutable untuk parameter filter jadwal dokter
 * (`/jadwal-dokter` dan komponen Livewire `DoctorScheduleFilter`).
 *
 * Properti:
 * - `polyclinicId` : id Polyclinic (positif) atau null bila tidak difilter.
 * - `day`          : enum {@see Day} atau null bila tidak difilter.
 * - `search`       : string (≥ 2 karakter saat dipakai) atau null.
 *
 * Validates: Requirements 4.7, 4.8.
 */
final readonly class ScheduleFilter
{
    public function __construct(
        public ?int $polyclinicId = null,
        public ?Day $day = null,
        public ?string $search = null,
    ) {
    }

    /**
     * Bangun DTO dari array (mis. data hasil FormRequest::validated()).
     *
     * Menerima `day` baik sebagai instance Day maupun string backing
     * (SENIN..MINGGU). Nilai `polyclinic_id` / `polyclinicId` diterima
     * sebagai integer atau numeric-string. String `search` di-trim;
     * string kosong dinormalisasi menjadi `null`.
     *
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $polyclinicId = $data['polyclinicId'] ?? $data['polyclinic_id'] ?? null;
        if ($polyclinicId !== null && $polyclinicId !== '') {
            $polyclinicId = (int) $polyclinicId;
        } else {
            $polyclinicId = null;
        }

        $dayInput = $data['day'] ?? null;
        $day = match (true) {
            $dayInput instanceof Day => $dayInput,
            is_string($dayInput) && $dayInput !== '' => Day::from($dayInput),
            default => null,
        };

        $searchInput = $data['search'] ?? $data['q'] ?? null;
        $search = is_string($searchInput) ? trim($searchInput) : null;
        if ($search === '' || $search === null) {
            $search = null;
        }

        return new self($polyclinicId, $day, $search);
    }

    /**
     * Convenience constructor untuk dipakai langsung dengan
     * hasil `FormRequest::validated()` (lihat `ScheduleIndexRequest`).
     *
     * @param  array<string,mixed>  $validated
     */
    public static function fromRequest(array $validated): self
    {
        return self::fromArray($validated);
    }

    /**
     * Bentuk array kanonis untuk serialisasi cache key (lihat
     * `DoctorScheduleService::listFiltered`) dan round-trip dengan
     * {@see self::fromArray()}.
     *
     * @return array{polyclinicId: int|null, day: string|null, search: string|null}
     */
    public function toArray(): array
    {
        return [
            'polyclinicId' => $this->polyclinicId,
            'day'          => $this->day?->value,
            'search'       => $this->search,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->polyclinicId === null
            && $this->day === null
            && $this->search === null;
    }
}
