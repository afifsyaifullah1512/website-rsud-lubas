<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Tipe layanan rumah sakit (Service.type).
 *
 * Validates: Requirements 17.2.
 */
enum ServiceType: string
{
    case POLI = 'POLI';
    case RAWAT_INAP = 'RAWAT_INAP';
    case IGD = 'IGD';
    case PENUNJANG = 'PENUNJANG';
    case UNGGULAN = 'UNGGULAN';

    public function label(): string
    {
        return match ($this) {
            self::POLI => 'Poliklinik',
            self::RAWAT_INAP => 'Rawat Inap',
            self::IGD => 'Instalasi Gawat Darurat',
            self::PENUNJANG => 'Penunjang',
            self::UNGGULAN => 'Layanan Unggulan',
        };
    }

    /**
     * Label ringkas untuk badge/pill (mis. di kartu).
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::POLI => 'Poliklinik',
            self::RAWAT_INAP => 'Rawat Inap',
            self::IGD => 'IGD',
            self::PENUNJANG => 'Penunjang',
            self::UNGGULAN => 'Unggulan',
        };
    }

    /**
     * Deskripsi singkat group untuk header section.
     */
    public function description(): string
    {
        return match ($this) {
            self::UNGGULAN => 'Layanan andalan dengan dokter spesialis dan fasilitas terkini.',
            self::POLI => 'Pelayanan rawat jalan oleh dokter spesialis sesuai jadwal.',
            self::IGD => 'Penanganan kegawatdaruratan medis 24 jam.',
            self::RAWAT_INAP => 'Perawatan inap dengan pilihan kelas sesuai kebutuhan.',
            self::PENUNJANG => 'Layanan penunjang medis seperti laboratorium dan radiologi.',
        };
    }

    /**
     * Nama ikon Iconify (Material Symbols) default per tipe layanan.
     */
    public function iconName(): string
    {
        return match ($this) {
            self::IGD => 'ph:siren-duotone',
            self::RAWAT_INAP => 'ph:bed-duotone',
            self::PENUNJANG => 'ph:flask-duotone',
            self::UNGGULAN => 'ph:star-duotone',
            self::POLI => 'ph:stethoscope-duotone',
        };
    }

    /**
     * Heroicons outline path (single-path) per tipe layanan.
     * Dipakai oleh Blade untuk konsistensi visual antar halaman.
     */
    public function iconPath(): string
    {
        return match ($this) {
            // Bolt — respons cepat / gawat darurat 24 jam
            self::IGD => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
            // Gedung rumah sakit — rawat inap
            self::RAWAT_INAP => 'M2.25 21h19.5M4.5 21V5.25A2.25 2.25 0 016.75 3h6A2.25 2.25 0 0115 5.25V21M15 21V9.75A.75.75 0 0115.75 9h3A2.25 2.25 0 0121 11.25V21M8.25 6.75h.008v.008H8.25V6.75zm0 3h.008v.008H8.25V9.75zm0 3h.008v.008H8.25v-.008zm3-6h.008v.008h-.008V6.75zm0 3h.008v.008h-.008V9.75zm0 3h.008v.008h-.008v-.008z',
            // Beaker — laboratorium / penunjang medis
            self::PENUNJANG => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
            // Sparkles — layanan unggulan
            self::UNGGULAN => 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z',
            // Dokter/pasien — poliklinik (rawat jalan)
            self::POLI => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        };
    }

    /**
     * Tone warna kontekstual (Tailwind classes) untuk badge/icon-bg.
     */
    public function tone(): string
    {
        return match ($this) {
            self::IGD => 'rose',
            self::RAWAT_INAP => 'sky',
            self::PENUNJANG => 'amber',
            self::UNGGULAN => 'brand',
            self::POLI => 'violet',
        };
    }

    /**
     * Foto default per tipe (Unsplash) — dipakai bila Service tidak punya
     * gambar sendiri, agar kartu layanan tetap fotografis & menarik.
     */
    public function fallbackImage(): string
    {
        $id = match ($this) {
            self::IGD => 'photo-1587351021759-3e566b6af7cc',
            self::RAWAT_INAP => 'photo-1586773860418-d37222d8fce3',
            self::PENUNJANG => 'photo-1581595219315-a187dd40c322',
            self::UNGGULAN => 'photo-1551190822-a9333d879b1f',
            self::POLI => 'photo-1579684385127-1ef15d508118',
        };

        return "https://images.unsplash.com/{$id}?w=900&q=80&auto=format&fit=crop";
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
