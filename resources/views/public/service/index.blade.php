@extends('layouts.public')

@section('meta_title', 'Layanan')
@section('meta_description', 'Daftar lengkap pelayanan kesehatan RSUD: poliklinik, rawat inap, IGD, penunjang medis, dan layanan unggulan beserta detail dan jadwalnya.')

@section('content')
@include('partials._page-header', [
    'title' => 'Layanan',
    'description' => 'Daftar lengkap pelayanan kesehatan yang tersedia, dikelompokkan berdasarkan jenis layanan.',
    'breadcrumbs' => [['Layanan', null]],
])

@php
    use App\Support\Enums\ServiceType;
    $totalServices = collect($grouped)->sum(fn ($items) => $items->count());
    $imageUrl = static fn (?string $p): ?string => $p ? (\Illuminate\Support\Str::startsWith($p, 'http') ? $p : asset('storage/'.$p)) : null;

    // Kelas penuh (bukan dinamis) agar Tailwind tetap meng-compile.
    $toneClasses = [
        'rose'   => ['grad' => 'from-rose-100 to-rose-50',     'text' => 'text-rose-600',   'chip' => 'bg-rose-50 text-rose-700'],
        'sky'    => ['grad' => 'from-sky-100 to-sky-50',       'text' => 'text-sky-600',    'chip' => 'bg-sky-50 text-sky-700'],
        'amber'  => ['grad' => 'from-amber-100 to-amber-50',   'text' => 'text-amber-600',  'chip' => 'bg-amber-50 text-amber-700'],
        'brand'  => ['grad' => 'from-brand-100 to-brand-50',   'text' => 'text-brand-600',  'chip' => 'bg-brand-50 text-brand-700'],
        'violet' => ['grad' => 'from-violet-100 to-violet-50', 'text' => 'text-violet-600', 'chip' => 'bg-violet-50 text-violet-700'],
    ];
@endphp

{{-- Filter kategori (anchor) --}}
<section class="sticky top-16 lg:top-20 z-20 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="container-page py-3">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide text-sm">
            <a href="#semua" class="px-3.5 py-1.5 rounded-full bg-brand-600 text-white font-medium whitespace-nowrap shrink-0">
                Semua <span class="opacity-80">· {{ $totalServices }}</span>
            </a>
            @foreach ($types as $type)
                @php $items = $grouped[$type->value] ?? collect(); @endphp
                @if ($items->count() > 0)
                    <a href="#{{ strtolower($type->value) }}" class="px-3.5 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 font-medium whitespace-nowrap shrink-0 transition">
                        {{ $type->label() }} <span class="text-slate-400">· {{ $items->count() }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>

<section id="semua" class="container-page py-10 md:py-14 space-y-14">
    @forelse ($types as $type)
        @php
            $items = $grouped[$type->value] ?? collect();
            $tone = $toneClasses[$type->tone()] ?? $toneClasses['brand'];
        @endphp
        @if ($items->count() > 0)
            <div id="{{ strtolower($type->value) }}" x-data="rowCarousel()" class="scroll-mt-32">
                <div class="flex items-end justify-between gap-4 mb-5">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br {{ $tone['grad'] }} {{ $tone['text'] }} grid place-items-center shrink-0 shadow-soft">
                            <iconify-icon icon="{{ $type->iconName() }}" class="text-3xl" aria-hidden="true"></iconify-icon>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="font-display text-xl md:text-2xl font-bold text-slate-900">{{ $type->label() }}</h2>
                                <span class="inline-flex items-center rounded-full {{ $tone['chip'] }} px-2.5 py-0.5 text-xs font-semibold">{{ $items->count() }}</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-0.5">{{ $type->description() }}</p>
                        </div>
                    </div>

                    @if ($items->count() > 1)
                        <div class="hidden sm:flex items-center gap-2 shrink-0">
                            <button type="button" x-ref="prev" aria-label="Sebelumnya"
                                    class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" x-ref="next" aria-label="Berikutnya"
                                    class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="swiper py-2" x-ref="swiper">
                    <div class="swiper-wrapper">
                        @foreach ($items as $service)
                            <div class="swiper-slide h-auto">
                                @include('public.service._card', ['service' => $service])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @empty
        <p class="text-slate-500">Belum ada layanan dipublikasikan.</p>
    @endforelse
</section>
@endsection
