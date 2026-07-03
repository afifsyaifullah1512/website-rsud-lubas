@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $page->title ?: $pageTitle,
    'breadcrumbs' => [[$page->title ?: $pageTitle, null]],
])

<section class="container-page py-10 max-w-4xl">
    <article class="prose-rsud">
        {!! $safeBody !!}
    </article>

    @if ($pdfFiles->isNotEmpty())
        <div class="mt-10 border-t border-slate-200 pt-10">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Dokumen Terlampir</h2>
            <div class="grid gap-8">
                @foreach ($pdfFiles as $pdf)
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700 truncate">{{ basename($pdf->path) }}</span>
                            <a href="{{ Storage::disk($pdf->disk)->url($pdf->path) }}" download
                               class="text-sm text-brand-600 hover:text-brand-800 font-medium flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download
                            </a>
                        </div>
                        <iframe
                            src="{{ Storage::disk($pdf->disk)->url($pdf->path) }}"
                            class="w-full h-[600px] border-0"
                            title="{{ basename($pdf->path) }}">
                        </iframe>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>

@if ($pdfFiles->isNotEmpty())
    @push('head')
        <style>
            iframe { background: #f8fafc; }
        </style>
    @endpush
@endif
@endsection
