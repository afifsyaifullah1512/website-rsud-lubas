<?php

declare(strict_types=1);

use App\Http\Controllers\Public\ComplaintController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DoctorScheduleController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PpidController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TariffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publik
|--------------------------------------------------------------------------
|
| SecurityHeaders di-append global ke grup `web` (lihat bootstrap/app.php).
| Grup publik di sini menambahkan alias `public-cache` (Task 6.1) untuk
| header Cache-Control publik (Requirement 29.1, 30.4). Admin panel
| di-route otomatis oleh Filament pada `/admin`.
|
*/

Route::middleware('public-cache')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::prefix('profil')->group(function () {
        Route::get('/sejarah', [ProfileController::class, 'history'])->name('profil.sejarah');
        Route::get('/visi-misi', [ProfileController::class, 'visionMission'])->name('profil.visi-misi');
        Route::get('/struktur-organisasi', [ProfileController::class, 'structure'])->name('profil.struktur');
        Route::get('/sambutan-direktur', [ProfileController::class, 'directorMessage'])->name('profil.direktur');
    });

    Route::prefix('layanan')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('layanan.index');
        Route::get('/{slug}', [ServiceController::class, 'show'])->name('layanan.show');
    });

    Route::get('/jadwal-dokter', [DoctorScheduleController::class, 'index'])->name('jadwal');
    Route::get('/dokter/{slug}', [DoctorScheduleController::class, 'show'])->name('dokter.show');

    Route::prefix('berita')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('berita.index');
        Route::get('/kategori/{slug}', [NewsController::class, 'category'])->name('berita.kategori');
        Route::get('/{slug}', [NewsController::class, 'show'])->name('berita.show');
    });

    Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri');
    Route::get('/galeri/{slug}', [GalleryController::class, 'show'])->name('galeri.show');

    Route::get('/pendaftaran', [TariffController::class, 'registration'])->name('pendaftaran');

    Route::prefix('ppid')->group(function () {
        Route::get('/', [PpidController::class, 'index'])->name('ppid.index');
        Route::get('/dokumen/{id}', [PpidController::class, 'download'])->name('ppid.download');
        Route::get('/{type}', [PpidController::class, 'byType'])->name('ppid.type');
    });

    Route::get('/pengaduan', [ComplaintController::class, 'create'])->name('pengaduan.create');
    Route::post('/pengaduan', [ComplaintController::class, 'store'])
        ->middleware(['throttle:5,60', 'recaptcha'])
        ->name('pengaduan.store');
    Route::get('/pengaduan/terima-kasih/{ticket}', [ComplaintController::class, 'thanks'])
        ->name('pengaduan.thanks');
    Route::get('/pengaduan/cek/{ticket}', [ComplaintController::class, 'track'])
        ->name('pengaduan.track');

    Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq');

    // Halaman custom yang dibuat admin via Page resource. Slug bebas
    // selain yang sudah ditangani route khusus (sejarah, visi-misi, dll).
    Route::get('/halaman/{slug}', [PageController::class, 'show'])->name('halaman.show');

    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
});
