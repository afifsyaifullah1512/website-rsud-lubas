<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'recaptcha' => \App\Http\Middleware\RecaptchaV3::class,
            'security-headers' => \App\Http\Middleware\SecurityHeaders::class,
            'public-cache' => \App\Http\Middleware\ForcePublicCacheHeader::class,
        ]);

        // Header keamanan (HSTS, nosniff, X-Frame-Options, Referrer-Policy, CSP)
        // diterapkan ke seluruh grup `web` — termasuk panel admin Filament.
        // CSP sengaja permisif (`'unsafe-inline'`/`'unsafe-eval'`) agar
        // Alpine, Livewire, dan Filament tetap berfungsi. Header `Cache-Control:
        // public, max-age=300` yang agresif TIDAK dipasang global; ia tetap
        // opt-in lewat alias `public-cache` yang dipasang pada grup rute publik
        // (lihat routes/web.php Task 6.2) agar tidak meng-cache halaman admin
        // ber-state pengguna.
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Requirement 28.2: QueryException / database tidak tersedia saat
        // melayani permintaan web publik → tampilkan halaman maintenance 503
        // ber-branding RSUD yang self-contained (tidak menyentuh database).
        $exceptions->render(function (QueryException $e, $request) {
            // Saat debug mode aktif (lingkungan lokal/dev), biarkan exception
            // menggelembung agar developer melihat detail error aslinya.
            if (app()->hasDebugModeEnabled()) {
                return null;
            }

            // Pada perintah console (artisan/queue/scheduler), jangan render
            // view HTTP — biarkan penanganan error CLI bawaan berjalan.
            if (app()->runningInConsole()) {
                return null;
            }

            // Permintaan API/JSON menerima respons JSON 503, bukan halaman HTML.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Layanan sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
                ], 503);
            }

            return response()->view('errors.503', [], 503);
        });
    })->create();
