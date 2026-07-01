@php
    $settings = app(\App\Services\SiteSettingService::class);
    $phone = $settings->get('phone');
    $telHref = $phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : null;
@endphp
<section class="bg-white pb-12 md:pb-16">
    <div class="container-page">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 to-brand-900 px-6 py-10 md:px-12 md:py-12 text-white">
            <div class="absolute inset-0 opacity-10 deco-dots"></div>
            <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="max-w-xl">
                    <h2 class="font-display text-2xl md:text-3xl font-bold">{{ $settings->get('home_contact_heading', \App\Support\SiteContent::text('home_contact_heading')) }}</h2>
                    <p class="mt-2 text-brand-100/90">{{ $settings->get('home_contact_text', \App\Support\SiteContent::text('home_contact_text')) }}</p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    @if ($telHref)
                        <a href="{{ $telHref }}" class="btn-white">
                            <iconify-icon icon="ph:phone-call-duotone" class="text-lg"></iconify-icon>
                            {{ $phone }}
                        </a>
                    @endif
                    <a href="{{ route('kontak') }}" class="btn-outline">
                        <iconify-icon icon="ph:map-pin-duotone" class="text-lg"></iconify-icon>
                        Halaman Kontak
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
