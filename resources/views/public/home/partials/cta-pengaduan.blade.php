@php
    $settings = app(\App\Services\SiteSettingService::class);
    $phone = $settings->get('phone');
    $cHeading = $settings->get('home_complaint_heading', \App\Support\SiteContent::text('home_complaint_heading'));
    $cText = $settings->get('home_complaint_text', \App\Support\SiteContent::text('home_complaint_text'));
@endphp
<section class="bg-slate-100 border-t border-slate-200">
    <div class="container-page py-10 md:py-14 grid gap-8 md:grid-cols-2 items-center">
        <div>
            <h2 class="section-heading">{{ $cHeading }}</h2>
            <p class="text-slate-600 mt-2 max-w-xl">{{ $cText }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('pengaduan.create') }}" class="btn-primary">Form Pengaduan</a>
                <a href="{{ route('faq') }}" class="btn-ghost">Lihat FAQ</a>
            </div>
        </div>

        <div class="card p-5 space-y-3 text-sm">
            <p class="font-semibold text-slate-900">Kontak Cepat</p>
            <ul class="space-y-2 text-slate-700">
                @if ($phone)
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="ph:phone-call-duotone" class="mt-0.5 text-base text-brand-500"></iconify-icon>
                        <span>Hubungi: <a href="tel:{{ $phone }}" class="text-brand-700 hover:underline">{{ $phone }}</a></span>
                    </li>
                @endif
                <li class="flex items-start gap-2">
                    <iconify-icon icon="ph:ticket-duotone" class="mt-0.5 text-base text-brand-500"></iconify-icon>
                    <span>Setiap pengaduan diberi nomor tiket untuk pelacakan.</span>
                </li>
                <li class="flex items-start gap-2">
                    <iconify-icon icon="ph:envelope-duotone" class="mt-0.5 text-base text-brand-500"></iconify-icon>
                    <span>Konfirmasi tindak lanjut via email yang Anda daftarkan.</span>
                </li>
            </ul>
        </div>
    </div>
</section>
