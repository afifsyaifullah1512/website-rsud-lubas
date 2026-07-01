<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tambahkan header `Cache-Control: public, max-age=300` untuk halaman
 * publik tanpa state pengguna.
 *
 * Memenuhi Requirement 29.1 (TTL 5 menit untuk halaman publik).
 *
 * Middleware TIDAK menambahkan header bila:
 *   - Method bukan GET / HEAD (mutasi tidak boleh di-cache).
 *   - Pengguna sudah autentikasi (status user-spesifik).
 *   - Response sudah memiliki `Cache-Control` eksplisit.
 *   - Status code di luar 2xx (404/503 punya semantik berbeda).
 */
class ForcePublicCacheHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $response;
        }

        if ($request->user() !== null) {
            return $response;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }

        // Di lingkungan non-production (local/dev), jangan paksa cache 5 menit
        // supaya developer langsung melihat perubahan HTML/asset tanpa
        // hard-refresh manual.
        if (! app()->environment('production')) {
            $response->headers->set('Cache-Control', 'no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        if ($response->headers->has('Cache-Control')
            && $response->headers->get('Cache-Control') !== ''
        ) {
            $existing = (string) $response->headers->get('Cache-Control');
            if (! str_contains($existing, 'no-cache') && ! str_contains($existing, 'private')) {
                return $response;
            }
        }

        $response->headers->set('Cache-Control', 'public, max-age=300');

        return $response;
    }
}
