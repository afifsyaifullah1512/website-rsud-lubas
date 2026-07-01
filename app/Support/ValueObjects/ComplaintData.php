<?php

declare(strict_types=1);

namespace App\Support\ValueObjects;

/**
 * DTO immutable yang membawa payload pengaduan publik
 * dari `ComplaintController::store` ke `ComplaintService::submit`.
 *
 * Field selaras dengan Requirement 11.1–11.2:
 * - `name`    : 3..120 karakter
 * - `email`   : format email
 * - `phone`   : opsional, regex ^[0-9+\-() ]{8,20}$
 * - `subject` : ≤ 200 karakter
 * - `message` : 20..5000 karakter (akan disanitasi di service)
 *
 * Catatan privasi: instance ini boleh berada di memori request,
 * tetapi `message`, `email`, dan `phone` TIDAK boleh dilog
 * (lihat Requirement 32.2).
 *
 * Validates: Requirements 11.3.
 */
final readonly class ComplaintData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
        public ?string $phone = null,
    ) {
    }

    /**
     * Bangun DTO dari array (mis. `FormRequest::validated()`).
     *
     * Field opsional `phone` dinormalisasi: string kosong → null.
     * Semua field string di-trim agar perbandingan dan validasi
     * domain konsisten.
     *
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $phone = $data['phone'] ?? null;
        if (is_string($phone)) {
            $phone = trim($phone);
            if ($phone === '') {
                $phone = null;
            }
        } else {
            $phone = null;
        }

        return new self(
            name:    self::str($data, 'name'),
            email:   self::str($data, 'email'),
            subject: self::str($data, 'subject'),
            message: self::str($data, 'message'),
            phone:   $phone,
        );
    }

    /**
     * Convenience constructor untuk dipakai dari controller
     * dengan hasil `FormRequest::validated()`.
     *
     * @param  array<string,mixed>  $validated
     */
    public static function fromRequest(array $validated): self
    {
        return self::fromArray($validated);
    }

    /**
     * Representasi array kanonis untuk persistensi/serialisasi.
     *
     * @return array{name:string,email:string,subject:string,message:string,phone:string|null}
     */
    public function toArray(): array
    {
        return [
            'name'    => $this->name,
            'email'   => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'phone'   => $this->phone,
        ];
    }

    /**
     * Ambil dan normalisasi field string wajib dari array input.
     *
     * @param  array<string,mixed>  $data
     */
    private static function str(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }
}
