<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Formulir pengaduan publik (Requirement 11.1, 11.2).
 *
 * Komponen ini me-render form native yang melakukan POST ke route
 * `pengaduan.store`. Pemrosesan otoritatif (validasi, rate limit,
 * sanitasi, dan verifikasi reCAPTCHA v3) berlangsung di sisi server
 * melalui middleware `recaptcha`, `StoreComplaintRequest`, dan
 * `ComplaintService::submit` — bukan di Livewire — sehingga seluruh
 * jejak keamanan tetap berlaku meski JavaScript dimatikan.
 *
 * Tugas komponen:
 *  - Menampilkan field `name`, `email`, `phone`, `subject`, `message`,
 *    serta token reCAPTCHA v3 (Requirement 11.1).
 *  - Mempertahankan input lama (`old()`) ketika validasi server gagal
 *    dan request di-redirect kembali ke halaman form.
 *
 * Validates: Requirements 11.1, 11.2.
 */
class ComplaintForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $message = '';

    /**
     * Isi ulang nilai dari `old()` agar input pengadu tidak hilang
     * setelah redirect-back karena gagal validasi server.
     */
    public function mount(): void
    {
        $this->name = (string) old('name', '');
        $this->email = (string) old('email', '');
        $this->phone = (string) old('phone', '');
        $this->subject = (string) old('subject', '');
        $this->message = (string) old('message', '');
    }

    public function render(): View
    {
        return view('livewire.complaint-form', [
            'recaptchaSitekey' => (string) config('recaptchav3.sitekey', ''),
        ]);
    }
}
