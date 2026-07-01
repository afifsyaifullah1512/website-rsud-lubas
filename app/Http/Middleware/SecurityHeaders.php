<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tambahkan header keamanan respons publik.
 *
 * Memenuhi Requirement 30.4 dan 30.5:
 *  - `Strict-Transport-Security`  (HSTS, hanya HTTPS)
 *  - `X-Content-Type-Options: nosniff`
 *  - `X-Frame-Options: DENY`
 *  - `Referrer-Policy: same-origin`
 *  - `Content-Security-Policy` (default-src 'self' + map providers).
 *
 * Header ini di-apply pada seluruh route publik. Untuk admin panel,
 * Filament memiliki middleware sendiri.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS hanya bermakna pada HTTPS — tetap kirim untuk konsumen
        // proxy yang akan terminate TLS.
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'same-origin');

        // CSP: izinkan self + Maps embed dari Google atau OSM.
        // Catatan: Alpine.js dan Livewire menggunakan `new Function(...)`
        // (yang dianggap sebagai eval) untuk mengeksekusi ekspresi
        // direktif (`@click="open = !open"`, dll). Karena itu CSP
        // wajib menyertakan `'unsafe-eval'` untuk script-src — tanpa
        // ini, seluruh interaktivitas Alpine akan diam (dropdown,
        // toggle mobile menu, dll). `'unsafe-inline'` tetap diperlukan
        // untuk `<style>[x-cloak]{...}</style>` di head dan inline
        // event listener Livewire.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://maps.googleapis.com https://code.iconify.design; "
            ."style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
            ."img-src 'self' data: https:; "
            ."font-src 'self' https://fonts.bunny.net data:; "
            ."frame-src 'self' https://www.google.com https://www.openstreetmap.org; "
            ."connect-src 'self' https://api.iconify.design https://api.simplesvg.com https://api.unisvg.com;"
        );

        return $response;
    }
}
