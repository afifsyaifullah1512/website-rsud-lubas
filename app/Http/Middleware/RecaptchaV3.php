<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifikasi reCAPTCHA v3 untuk POST publik (Requirement 11.6, 30.5).
 *
 * Middleware mengecek token `g-recaptcha-response` ke API Google dengan
 * threshold skor minimal 0.5. Jika skor kurang atau token invalid, request
 * ditolak 422.
 *
 * Bila `RECAPTCHAV3_SECRET` kosong (lingkungan dev/test) atau request
 * berasal dari testing helper Laravel, middleware lewat tanpa verifikasi.
 */
class RecaptchaV3
{
    public function handle(Request $request, Closure $next, ?string $threshold = null): Response
    {
        $secret = (string) config('services.recaptcha.secret', env('RECAPTCHAV3_SECRET', ''));

        // Tidak konfigurasi → skip (Requirement 11.6 hanya berlaku jika diaktifkan).
        if ($secret === '' || app()->environment('testing')) {
            return $next($request);
        }

        $token = (string) $request->input('g-recaptcha-response', '');
        if ($token === '') {
            abort(422, 'Verifikasi gagal, silakan coba lagi.');
        }

        $minScore = (float) ($threshold ?? 0.5);

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

        $body = $response->json() ?? [];
        $success = (bool) ($body['success'] ?? false);
        $score = (float) ($body['score'] ?? 0.0);

        if (! $success || $score < $minScore) {
            abort(422, 'Verifikasi gagal, silakan coba lagi.');
        }

        return $next($request);
    }
}
