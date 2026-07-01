@extends('layouts.public')

@section('meta_title', $service->name)
@section('meta_description', \Illuminate\Support\Str::limit(trim(strip_tags((string) $service->description)) ?: ($service->polyclinic?->name ? 'Layanan '.$service->name.' di '.$service->polyclinic->name.'.' : 'Informasi layanan '.$service->name.' di RSUD.'), 160))

@section('content')
@php
    use App\Support\Enums\ServiceType;
    $type = $service->type instanceof ServiceType ? $service->type : ServiceType::tryFrom((string) $service->type);
    $tone = match ($type?->tone() ?? 'brand') {
        'rose'   => ['bg' => 'bg-rose-50',   'text' => 'text-rose-700',   'border' => 'border-rose-200'],
        'sky'    => ['bg' => 'bg-sky-50',    'text' => 'text-sky-700',    'border' => 'border-sky-200'],
        'amber'  => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-200'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-200'],
        default  => ['bg' => 'bg-brand-50',  'text' => 'text-brand-700',  'border' => 'border-brand-200'],
    };
@endphp

<section class="bg-white border-b border-slate-200">
    <div class="container-page py-8 md:py-10">
        <nav class="text-xs text-slate-500 mb-3 flex flex-wrap items-center gap-1.5">
            <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('layanan.index') }}" class="hover:text-brand-700">Layanan</a>
            @if ($type)
                <span>/</span>
                <a href="{{ route('layanan.index') }}#{{ strtolower($type->value) }}" class="hover:text-brand-700">{{ $type->label() }}</a>
            @endif
            <span>/</span>
            <span class="text-slate-700">{{ $service->name }}</span>
        </nav>

        <div class="flex items-start gap-4">
            @if ($type)
                <div class="h-12 w-12 rounded-md {{ $tone['bg'] }} {{ $tone['text'] }} grid place-items-center shrink-0">
                    <iconify-icon icon="{{ $type->iconName() }}" class="text-3xl" aria-hidden="true"></iconify-icon>
                </div>
            @endif
            <div class="min-w-0">
                @if ($type)
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $tone['text'] }}">{{ $type->label() }}</p>
                @endif
                <h1 class="font-display text-2xl md:text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ $service->name }}</h1>
                @if ($service->polyclinic)
                    <p class="text-sm text-slate-600 mt-1">
                        <span class="text-slate-500">Poliklinik:</span> {{ $service->polyclinic->name }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="container-page py-10 grid gap-8 lg:grid-cols-3">
    <article class="lg:col-span-2 space-y-6">
        <div class="card p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">Tentang Layanan</h2>
            <div class="prose-rsud">
                @if ($service->description)
                    {!! nl2br(e($service->description)) !!}
                @else
                    <p class="text-slate-500">Deskripsi layanan belum tersedia.</p>
                @endif
            </div>
        </div>

        @if (! empty($doctors) && $doctors->isNotEmpty())
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Dokter di {{ $service->polyclinic?->name }}</h2>
                    @if ($service->polyclinic)
                        <a href="{{ route('jadwal', ['polyclinic_id' => $service->polyclinic_id]) }}" class="text-xs text-brand-700 hover:underline">Lihat jadwal →</a>
                    @endif
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($doctors as $doctor)
                        <a href="{{ route('dokter.show', $doctor->slug) }}" class="flex items-center gap-3 p-3 rounded-md border border-slate-200 hover:border-brand-300 hover:bg-slate-50 transition">
                            @if ($doctor->photo)
                                <img src="{{ str_starts_with($doctor->photo, 'http') ? $doctor->photo : asset('storage/'.$doctor->photo) }}" alt="{{ $doctor->name }}" class="h-10 w-10 rounded-full object-cover bg-slate-100">
                            @else
                                <div class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center text-slate-400">
                                    <iconify-icon icon="ph:user-duotone" class="text-2xl"></iconify-icon>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900 text-sm truncate">{{ $doctor->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $doctor->specialization }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </article>

    <aside class="space-y-4">
        @if ($service->polyclinic)
            <div class="card p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Poliklinik</p>
                <p class="font-semibold text-slate-900 mt-1">{{ $service->polyclinic->name }}</p>
                @if ($service->polyclinic->description)
                    <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $service->polyclinic->description }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <a href="{{ route('jadwal', ['polyclinic_id' => $service->polyclinic_id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-brand-50 text-brand-700 hover:bg-brand-100 font-medium">
                        Jadwal Dokter
                    </a>
                </div>
            </div>
        @endif

        <div class="card p-5">
            <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Butuh bantuan?</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li>
                    <a href="{{ route('pendaftaran') }}" class="flex items-center gap-2 text-slate-700 hover:text-brand-700">
                        <iconify-icon icon="ph:clipboard-text-duotone" class="text-lg text-brand-500"></iconify-icon>
                        Cara pendaftaran pasien
                    </a>
                </li>
                <li>
                    <a href="{{ route('faq') }}" class="flex items-center gap-2 text-slate-700 hover:text-brand-700">
                        <iconify-icon icon="ph:question-duotone" class="text-lg text-brand-500"></iconify-icon>
                        Pertanyaan umum (FAQ)
                    </a>
                </li>
                <li>
                    <a href="{{ route('kontak') }}" class="flex items-center gap-2 text-slate-700 hover:text-brand-700">
                        <iconify-icon icon="ph:phone-call-duotone" class="text-lg text-brand-500"></iconify-icon>
                        Hubungi kami
                    </a>
                </li>
            </ul>
        </div>

        @if (! empty($relatedServices) && $relatedServices->isNotEmpty())
            <div class="card p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-3">Layanan Sejenis</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($relatedServices as $rs)
                        <li>
                            <a href="{{ route('layanan.show', $rs->slug) }}" class="flex items-start gap-2 text-slate-700 hover:text-brand-700">
                                <svg class="h-4 w-4 text-slate-300 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                <span>{{ $rs->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </aside>
</section>
@endsection
