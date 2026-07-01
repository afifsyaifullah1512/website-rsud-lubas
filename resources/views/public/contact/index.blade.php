@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Kontak',
    'description' => 'Informasi kontak dan lokasi rumah sakit.',
    'breadcrumbs' => [['Kontak', null]],
])

@php
    $settings = app(\App\Services\SiteSettingService::class);
    $address = $settings->get('address');
    $phone = $settings->get('phone');
    $email = $settings->get('email');
    $hours = $settings->get('operational_hours');
    $lat = $settings->get('latitude');
    $lng = $settings->get('longitude');
    $facebook = $settings->get('social_facebook');
    $instagram = $settings->get('social_instagram');
    $youtube = $settings->get('social_youtube');
    $hasSocial = $facebook || $instagram || $youtube;
@endphp

<section class="container-page py-10 grid gap-8 lg:grid-cols-2">
    <div class="card p-5 space-y-4 text-sm">
        @if ($address)
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Alamat</p>
                <p class="text-slate-700 mt-1">{{ $address }}</p>
            </div>
        @endif
        @if ($phone)
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Telepon</p>
                <p class="mt-1"><a href="tel:{{ $phone }}" class="text-brand-700 hover:underline">{{ $phone }}</a></p>
            </div>
        @endif
        @if ($email)
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Email</p>
                <p class="mt-1"><a href="mailto:{{ $email }}" class="text-brand-700 hover:underline">{{ $email }}</a></p>
            </div>
        @endif
        @if ($hours)
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Jam Operasional</p>
                <p class="text-slate-700 mt-1">{{ $hours }}</p>
            </div>
        @endif
        @if ($hasSocial)
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Media Sosial</p>
                <div class="flex items-center gap-2 mt-2">
                    @if ($facebook)
                        <a href="{{ $facebook }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-md bg-slate-100 hover:bg-brand-50 text-brand-700 grid place-items-center transition" aria-label="Facebook">
                            <iconify-icon icon="ph:facebook-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                    @if ($instagram)
                        <a href="{{ $instagram }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-md bg-slate-100 hover:bg-brand-50 text-brand-700 grid place-items-center transition" aria-label="Instagram">
                            <iconify-icon icon="ph:instagram-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                    @if ($youtube)
                        <a href="{{ $youtube }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-md bg-slate-100 hover:bg-brand-50 text-brand-700 grid place-items-center transition" aria-label="YouTube">
                            <iconify-icon icon="ph:youtube-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($lat && $lng)
        <div class="card overflow-hidden aspect-video">
            <iframe
                title="Peta lokasi"
                width="100%" height="100%" loading="lazy"
                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lng - 0.005 }}%2C{{ $lat - 0.005 }}%2C{{ $lng + 0.005 }}%2C{{ $lat + 0.005 }}&layer=mapnik&marker={{ $lat }}%2C{{ $lng }}"
                class="border-0 w-full h-full"
            ></iframe>
        </div>
    @endif
</section>
@endsection
