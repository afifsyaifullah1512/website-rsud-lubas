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
        <div class="mt-12 border-t border-slate-100 pt-10">
            <div class="flex items-center gap-3 mb-8">
                <div class="h-8 w-1.5 rounded-full bg-brand-600"></div>
                <h2 class="text-lg font-bold text-slate-800">Dokumen Terlampir</h2>
                <span class="text-xs text-slate-400 font-medium ml-1">{{ $pdfFiles->count() }} file</span>
            </div>
            <div class="grid gap-6">
                @foreach ($pdfFiles as $pdf)
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-10 w-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-700 truncate">{{ basename($pdf->path) }}</p>
                                    @if ($pdf->size)
                                        <p class="text-xs text-slate-400">{{ number_format($pdf->size / 1024) }} KB</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="{{ Storage::disk($pdf->disk)->url($pdf->path) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                                    Fullscreen
                                </a>
                                <a href="{{ Storage::disk($pdf->disk)->url($pdf->path) }}" download
                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download
                                </a>
                            </div>
                        </div>
                        <div class="bg-slate-50">
                            <iframe
                                src="{{ Storage::disk($pdf->disk)->url($pdf->path) }}#view=FitH"
                                class="w-full h-[75vh] border-0"
                                title="{{ basename($pdf->path) }}">
                            </iframe>
                        </div>
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
