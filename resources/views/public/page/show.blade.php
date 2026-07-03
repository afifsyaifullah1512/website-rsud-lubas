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
                        <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span class="text-sm font-semibold text-slate-700 truncate">{{ basename($pdf->path) }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ Storage::disk($pdf->disk)->url($pdf->path) }}" target="_blank"
                                   class="text-sm text-slate-500 hover:text-slate-700 font-medium flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                                    Fullscreen
                                </a>
                                <a href="{{ Storage::disk($pdf->disk)->url($pdf->path) }}" download
                                   class="text-sm text-brand-600 hover:text-brand-800 font-semibold flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download
                                </a>
                            </div>
                        </div>
                        <iframe
                            src="{{ Storage::disk($pdf->disk)->url($pdf->path) }}#view=FitH"
                            class="w-full h-[85vh] border-0 bg-slate-100"
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
