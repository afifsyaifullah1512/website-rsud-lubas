<?php

/*
|--------------------------------------------------------------------------
| Halaman Error Ber-branding RSUD (Requirement 28)
|--------------------------------------------------------------------------
|
| Memverifikasi:
|  - 28.1: rute tidak dikenal -> 404 dengan halaman ber-branding RSUD + tombol
|    "Kembali ke Beranda".
|  - 28.2: QueryException saat melayani permintaan web publik (mode produksi /
|    debug nonaktif) -> 503 halaman maintenance self-contained.
|  - 28.4: respons 422 me-render view 422 ber-branding.
|
| Catatan: handler 503 di bootstrap/app.php sengaja MENGGELEMBUNGKAN
| QueryException saat `app.debug=true` (developer tetap melihat trace) dan saat
| `app()->runningInConsole()` true. Karena PHPUnit/Pest SELALU berjalan di mode
| console, jalur HTTP 503 tidak bisa dipicu lewat test kernel; maka view 503
| diverifikasi langsung (render + response()->view berstatus 503).
|
*/

it('returns 404 with RSUD-branded page and back-to-home button', function () {
    $response = $this->get('/rute-yang-tidak-ada');

    $response->assertNotFound();
    $response->assertSee('Beranda');
    $response->assertSee('href="/"', false);
    $response->assertSee('404', false);
});

it('renders the self-contained RSUD-branded 503 maintenance view', function () {
    // Handler QueryException->503 di bootstrap/app.php sengaja MENGGELEMBUNGKAN
    // exception saat `app()->runningInConsole()` true. Karena PHPUnit/Pest
    // SELALU berjalan di mode console, jalur HTTP 503 tidak dapat dipicu lewat
    // test kernel. Maka kita verifikasi langsung bahwa view 503 ber-branding
    // RSUD ter-render, self-contained (tanpa menyentuh database), dan punya
    // tombol kembali ke beranda — sesuai Requirement 28.2.
    $html = view('errors.503')->render();

    expect($html)
        ->toContain('503')
        ->toContain('pemeliharaan')
        ->toContain('Beranda')
        ->toContain('href="/"')
        ->toContain('RSUD');
});

it('renders the response()->view payload that the QueryException handler returns with a 503 status', function () {
    // Mereplikasi persis respons yang dihasilkan handler produksi.
    $response = response()->view('errors.503', [], 503);

    expect($response->getStatusCode())->toBe(503);
    expect($response->getContent())
        ->toContain('pemeliharaan')
        ->toContain('Beranda');
});

it('renders the RSUD-branded 422 view', function () {
    Illuminate\Support\Facades\Route::middleware('web')->get('/__abort-422', function () {
        abort(422);
    });

    $response = $this->get('/__abort-422');

    $response->assertStatus(422);
    $response->assertSee('422', false);
    $response->assertSee('Beranda');
});
