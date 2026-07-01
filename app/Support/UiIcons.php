<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Registry path SVG (stroke, viewBox 24x24) yang dipakai bersama oleh
 * komponen front-end (quick actions, trust badges) dan form admin
 * (SiteSettingPage) agar ikon dapat dipilih dari panel admin.
 */
final class UiIcons
{
    /**
     * Pemetaan key → nama ikon Iconify (Material Symbols, gaya filled/rounded).
     * Dipakai komponen front-end via <iconify-icon>. Jauh lebih rapi
     * dibanding outline tipis.
     *
     * @return array<string,string>
     */
    public static function iconifyMap(): array
    {
        return [
            'calendar' => 'ph:calendar-dots-duotone',
            'clipboard' => 'ph:clipboard-text-duotone',
            'money' => 'ph:money-duotone',
            'chat' => 'ph:chat-circle-dots-duotone',
            'shield' => 'ph:seal-check-duotone',
            'card' => 'ph:identification-card-duotone',
            'bolt' => 'ph:siren-duotone',
            'document' => 'ph:file-text-duotone',
            'phone' => 'ph:phone-call-duotone',
            'heart' => 'ph:heartbeat-duotone',
            'building' => 'ph:hospital-duotone',
            'stethoscope' => 'ph:stethoscope-duotone',
            'info' => 'ph:info-duotone',
            'star' => 'ph:star-duotone',
            'eye' => 'ph:eye-duotone',
            'plus' => 'ph:first-aid-kit-duotone',
            'scissors' => 'ph:scissors-duotone',
            'ambulance' => 'ph:ambulance-duotone',
            'cap' => 'ph:graduation-cap-duotone',
            'home' => 'ph:house-duotone',
            'pin' => 'ph:map-pin-duotone',
            'clock' => 'ph:clock-duotone',
            'drop' => 'ph:drop-duotone',
            'pulse' => 'ph:heartbeat-duotone',
            'users' => 'ph:users-duotone',
            'gallery' => 'ph:images-duotone',
            'flask' => 'ph:flask-duotone',
            'pill' => 'ph:pill-duotone',
            'radioactive' => 'ph:radioactive-duotone',
            'bed' => 'ph:bed-duotone',
            'siren' => 'ph:siren-duotone',
            'first-aid' => 'ph:first-aid-kit-duotone',
            'tooth' => 'ph:tooth-duotone',
            'baby' => 'ph:baby-duotone',
        ];
    }

    /**
     * Nama ikon Iconify untuk satu key (fallback ke bintang).
     */
    public static function iconify(?string $key): string
    {
        $map = self::iconifyMap();

        return $map[$key] ?? $map['star'];
    }

    /**
     * @return array<string,string> key => path "d" SVG
     */
    public static function paths(): array
    {
        return [
            'calendar' => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
            'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'money' => 'M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6',
            'chat' => 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',
            'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            'bolt' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'document' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'phone' => 'M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z',
            'heart' => 'M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 10-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z',
            'building' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9v.01M9 12v.01M9 15v.01',
            'stethoscope' => 'M4.8 2.3A.3.3 0 105 2H4a2 2 0 00-2 2v5a6 6 0 006 6v0a6 6 0 006-6V4a2 2 0 00-2-2h-1a.2.2 0 10.3.3M14 15.9l2 4.1M8 15.9l-2 4.1M12 15v5',
            'info' => 'M12 16v-4M12 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'star' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
            'heart' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
            'eye' => 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'plus' => 'M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
            'scissors' => 'M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l10.866 6.272M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025',
            'ambulance' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
            'cap' => 'M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
            'home' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            'pin' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
            'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            'drop' => 'M12 21a8.25 8.25 0 005.834-14.084L12 2.25 6.166 6.916A8.25 8.25 0 0012 21z',
            'pulse' => 'M3.75 12h3.857l1.5-4.5 3 9 1.5-4.5h4.643',
            'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        ];
    }

    /**
     * Path untuk satu key, fallback ke 'star' bila tidak dikenal.
     */
    public static function path(?string $key): string
    {
        $paths = self::paths();

        return $paths[$key] ?? $paths['star'];
    }

    /**
     * Opsi untuk Filament Select (key => label terbaca).
     *
     * @return array<string,string>
     */
    public static function options(): array
    {
        return [
            'calendar' => 'Kalender',
            'clipboard' => 'Papan klip / Daftar',
            'money' => 'Biaya / Rupiah',
            'chat' => 'Chat / Pengaduan',
            'shield' => 'Perisai / Akreditasi',
            'card' => 'Kartu / BPJS',
            'bolt' => 'Petir / Cepat',
            'document' => 'Dokumen',
            'phone' => 'Telepon',
            'heart' => 'Hati / Peduli',
            'building' => 'Gedung',
            'stethoscope' => 'Stetoskop',
            'info' => 'Informasi',
            'star' => 'Bintang',
            'heart' => 'Hati / Jantung',
            'eye' => 'Mata',
            'plus' => 'Plus / Medis',
            'scissors' => 'Bedah (gunting)',
            'ambulance' => 'Ambulans',
            'cap' => 'Pendidikan / Toga',
            'home' => 'Rumah',
            'pin' => 'Lokasi / Peta',
            'clock' => 'Jam',
            'drop' => 'Tetes / Darah',
            'pulse' => 'Detak / Pulse',
            'users' => 'Orang banyak',
            'gallery' => 'Galeri / Foto',
            'flask' => 'Laboratorium (labu)',
            'pill' => 'Farmasi (pil)',
            'radioactive' => 'Radiologi',
            'bed' => 'Tempat tidur / Rawat inap',
            'siren' => 'Sirine / IGD',
            'first-aid' => 'Kotak P3K',
            'tooth' => 'Gigi',
            'baby' => 'Bayi / Anak',
        ];
    }
}
