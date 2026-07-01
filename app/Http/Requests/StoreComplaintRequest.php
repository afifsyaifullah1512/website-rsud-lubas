<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi pengaduan publik (Requirement 11.2).
 *
 * Aturan:
 *  - name    : 3..120 karakter
 *  - email   : format email
 *  - phone   : opsional, regex ^[0-9+\-() ]{8,20}$
 *  - subject : ≤ 200 karakter
 *  - message : 20..5000 karakter
 *  - g-recaptcha-response : valid (verifikasi di middleware `recaptcha`)
 */
class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-() ]{8,20}$/'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Nama minimal 3 karakter.',
            'email.email' => 'Format email tidak valid.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi 0-9, +, -, (, ), dan spasi (8–20 karakter).',
            'message.min' => 'Pesan pengaduan minimal 20 karakter.',
            'message.max' => 'Pesan pengaduan maksimal 5000 karakter.',
        ];
    }
}
