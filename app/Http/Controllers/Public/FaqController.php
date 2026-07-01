<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\View\View;

/**
 * Halaman FAQ publik (Requirement 13.1–13.2).
 */
class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.faq.index', [
            'pageTitle' => 'Pertanyaan yang Sering Diajukan',
            'faqs' => $faqs,
        ]);
    }
}
