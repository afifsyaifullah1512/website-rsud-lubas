<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Berkas konfigurasi Pest untuk proyek Website RSUD. Mengikat TestCase pada
| seluruh berkas test di direktori Feature dan Property agar mendapat akses
| Laravel application bootstrap. Test di direktori Unit menggunakan
| PHPUnit\Framework\TestCase agar tetap ringan dan cepat.
|
*/

// Stub Redis class agar lingkungan tanpa ekstensi `phpredis` tidak crash
// jika ada package pihak ketiga yang menyentuh Redis::connection() saat
// boot/runtime test (mis. cache PermissionRegistrar yang sudah ter-resolve
// sebelum konfigurasi cache di-override). Stub ini idempoten: hanya
// terdaftar bila kelas Redis belum tersedia.
require_once __DIR__.'/stubs/RedisStub.php';

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Property');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Tempat pendaftaran custom expectation untuk Pest. Tambahkan ekspektasi
| domain spesifik (misal: ekspektasi untuk ScheduleFilter, NewsStatus, dll)
| pada saat dibutuhkan oleh tugas terkait.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Helper global untuk test. Letakkan helper kecil di sini agar dapat
| dipanggil tanpa import; helper kompleks dipisahkan ke kelas tersendiri.
|
*/

function something(): void
{
    // ...
}
