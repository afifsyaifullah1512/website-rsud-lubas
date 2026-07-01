<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

/**
 * Alur pendaftaran pasien (Requirement 8.3).
 *
 * Catatan: fitur Tarif publik dinonaktifkan; controller ini kini hanya
 * melayani halaman alur pendaftaran.
 */
class TariffController extends Controller
{
    public function registration(): View
    {
        $page = Page::query()->where('slug', 'pendaftaran')->first();
        $rawBody = (string) ($page?->body ?? '');
        $body = $rawBody;
        try {
            if ($page && class_exists(Purifier::class)) {
                $body = Purifier::clean($rawBody);
            }
        } catch (\Throwable) {
            $body = $rawBody;
        }

        return view('public.tariff.registration', [
            'pageTitle' => 'Alur Pendaftaran',
            'page' => $page,
            'safeBody' => $body,
        ]);
    }
}
