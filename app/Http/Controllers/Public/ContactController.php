<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Halaman Kontak publik (Requirement 12.1–12.3).
 *
 * Seluruh informasi kontak diambil dari `SiteSetting` melalui partial
 * `_footer` dan view kontak; controller ini hanya menyajikan layout.
 */
class ContactController extends Controller
{
    public function index(): View
    {
        return view('public.contact.index', [
            'pageTitle' => 'Kontak',
        ]);
    }
}
