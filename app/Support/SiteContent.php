<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sumber tunggal nilai default untuk konten front-end yang dapat
 * diedit dari panel admin (SiteSettingPage). Blade memakai nilai
 * SiteSetting bila ada, dan jatuh balik ke default di sini.
 */
final class SiteContent
{
    /**
     * Teks skalar editable: key SiteSetting => nilai default.
     *
     * @var array<string,string>
     */
    public const TEXTS = [
        'footer_tagline' => 'Pemerintah Kabupaten Agam',

        'home_services_eyebrow' => 'Apa yang kami tawarkan',
        'home_services_heading' => 'Layanan Unggulan',
        'home_services_subheading' => 'Layanan kesehatan utama dengan dukungan tenaga medis profesional dan fasilitas modern.',

        'home_schedule_heading' => 'Jadwal Dokter Hari',
        'home_schedule_subheading' => 'Daftar dokter yang praktik pada hari ini.',

        'home_news_heading' => 'Berita & Pengumuman',
        'home_news_subheading' => 'Informasi terbaru seputar layanan dan kegiatan rumah sakit.',

        'home_complaint_heading' => 'Sampaikan Pengaduan',
        'home_complaint_text' => 'Setiap pengaduan akan kami tindak lanjuti secara transparan. Anda dapat melacak status melalui nomor tiket yang diberikan setelah pengaduan terkirim.',

        'home_about_heading' => '',
        'home_about_text' => '',
        'home_about_eyebrow' => 'Tentang Kami',

        'home_facilities_eyebrow' => 'Fasilitas',
        'home_facilities_heading' => 'Fasilitas & Penunjang Medis',
        'home_facilities_subheading' => 'Didukung fasilitas lengkap untuk pelayanan yang cepat dan akurat.',

        'home_gallery_eyebrow' => 'Dokumentasi',
        'home_gallery_heading' => 'Galeri',
        'home_gallery_subheading' => 'Momen kegiatan dan fasilitas RSUD.',

        'home_contact_heading' => 'Butuh informasi atau bantuan?',
        'home_contact_text' => 'Tim kami siap membantu. Hubungi kami atau kunjungi halaman kontak untuk informasi lokasi dan layanan.',
    ];

    /**
     * Default poin highlight pada section "Tentang" beranda.
     *
     * @return array<int,array{text:string}>
     */
    public static function aboutHighlights(): array
    {
        return [
            ['text' => 'Terakreditasi Paripurna (KARS)'],
            ['text' => 'Dokter spesialis berpengalaman'],
            ['text' => 'Layanan IGD siaga 24 jam'],
        ];
    }

    /**
     * Default daftar Fasilitas & Penunjang Medis beranda.
     *
     * @return array<int,array{name:string,icon:string}>
     */
    public static function facilities(): array
    {
        return [
            ['name' => 'IGD 24 Jam', 'icon' => 'siren'],
            ['name' => 'Laboratorium', 'icon' => 'flask'],
            ['name' => 'Radiologi', 'icon' => 'radioactive'],
            ['name' => 'Farmasi', 'icon' => 'pill'],
            ['name' => 'Ambulans', 'icon' => 'ambulance'],
            ['name' => 'Rawat Inap', 'icon' => 'bed'],
        ];
    }

    public static function text(string $key): string
    {
        return self::TEXTS[$key] ?? '';
    }

    /**
     * Default daftar Aksi Cepat (quick actions) beranda.
     *
     * @return array<int,array{label:string,description:string,url:string,icon:string}>
     */
    public static function quickActions(): array
    {
        return [
            ['label' => 'Jadwal Dokter', 'description' => 'Cari dokter & jam praktik', 'url' => route('jadwal'), 'icon' => 'calendar'],
            ['label' => 'Pendaftaran', 'description' => 'Alur pasien rawat jalan/inap', 'url' => route('pendaftaran'), 'icon' => 'clipboard'],
            ['label' => 'Pengaduan', 'description' => 'Sampaikan keluhan & saran', 'url' => route('pengaduan.create'), 'icon' => 'chat'],
        ];
    }

    /**
     * Default daftar Keunggulan (trust badges) beranda.
     *
     * @return array<int,array{label:string,icon:string}>
     */
    public static function trustBadges(): array
    {
        return [
            ['label' => 'Akreditasi Paripurna', 'icon' => 'shield'],
            ['label' => 'BPJS Diterima', 'icon' => 'card'],
            ['label' => 'Respons Cepat', 'icon' => 'bolt'],
            ['label' => 'Catatan Medis Aman', 'icon' => 'document'],
        ];
    }
}
