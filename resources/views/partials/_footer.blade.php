@php
    $settings = app(\App\Services\SiteSettingService::class);
    $rsName = $settings->get('rs_name', 'RSUD');
    $rsDesc = $settings->get('rs_description');
    $logo = $settings->get('logo');
    $address = $settings->get('address', '');
    $phone = $settings->get('phone', '');
    $email = $settings->get('email', '');
    $hours = $settings->get('operational_hours', '');
    $facebook = $settings->get('social_facebook');
    $instagram = $settings->get('social_instagram');
    $youtube = $settings->get('social_youtube');
    $tagline = $settings->get('footer_tagline', \App\Support\SiteContent::text('footer_tagline'));
    $ppidActive = (bool) $settings->get('ppid_active', true);
    $karirActive = (bool) $settings->get('karir_active', true);
@endphp

<footer class="mt-16 text-brand-100">
    {{-- Accent strip warna --}}
    <div class="h-1.5 bg-brand-600"></div>
    <div class="bg-brand-800">
    <div class="container-page py-12 grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <div class="flex items-center gap-3">
                @if ($logo)
                    <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/'.$logo) }}" alt="{{ $rsName }}" class="h-12 w-12 object-contain bg-white rounded-md p-1">
                @else
                    <div class="h-11 w-11 rounded-xl bg-white/15 ring-1 ring-white/20 grid place-items-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                @endif
                <div>
                    <p class="font-display font-bold text-white">{{ $rsName }}</p>
                    <p class="text-xs text-brand-200">{{ $tagline }}</p>
                </div>
            </div>
            @if ($rsDesc)
                <p class="text-sm leading-relaxed mt-4 text-brand-100/80">{{ $rsDesc }}</p>
            @endif
        </div>

        <div class="lg:col-span-3">
            <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Kontak</h3>
            <ul class="space-y-2 text-sm text-brand-100/90">
                @if ($address)
                    <li>{{ $address }}</li>
                @endif
                @if ($phone)
                    <li><a href="tel:{{ $phone }}" class="hover:text-white transition">Telepon: {{ $phone }}</a></li>
                @endif
                @if ($email)
                    <li><a href="mailto:{{ $email }}" class="hover:text-white transition">Email: {{ $email }}</a></li>
                @endif
                @if ($hours)
                    <li>Jam Operasional: {{ $hours }}</li>
                @endif
            </ul>
        </div>

        <div class="lg:col-span-3">
            <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Tautan</h3>
            <ul class="grid grid-cols-2 gap-y-2 text-sm text-brand-100/90">
                <li><a href="{{ route('layanan.index') }}" class="hover:text-white transition">Layanan</a></li>
                <li><a href="{{ route('jadwal') }}" class="hover:text-white transition">Jadwal Dokter</a></li>
                <li><a href="{{ route('berita.index') }}" class="hover:text-white transition">Berita</a></li>
                @if ($ppidActive)
                    <li><a href="{{ route('ppid.index') }}" class="hover:text-white transition">PPID</a></li>
                @endif
                <li><a href="{{ route('pengaduan.create') }}" class="hover:text-white transition">Pengaduan</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQ</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <h3 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Sosial Media</h3>
            <div class="flex items-center gap-2">
                @if ($facebook)
                    <a href="{{ $facebook }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-lg bg-white/10 ring-1 ring-white/15 text-white hover:bg-white/20 grid place-items-center transition" aria-label="Facebook">
                        <iconify-icon icon="ph:facebook-logo-duotone" class="text-xl"></iconify-icon>
                    </a>
                @endif
                @if ($instagram)
                    <a href="{{ $instagram }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-lg bg-white/10 ring-1 ring-white/15 text-white hover:bg-white/20 grid place-items-center transition" aria-label="Instagram">
                        <iconify-icon icon="ph:instagram-logo-duotone" class="text-xl"></iconify-icon>
                    </a>
                @endif
                @if ($youtube)
                    <a href="{{ $youtube }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-lg bg-white/10 ring-1 ring-white/15 text-white hover:bg-white/20 grid place-items-center transition" aria-label="YouTube">
                        <iconify-icon icon="ph:youtube-logo-duotone" class="text-xl"></iconify-icon>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page py-4 text-xs text-brand-200 flex flex-col sm:flex-row justify-between gap-2">
            <span>© {{ date('Y') }} Afif Syaifullah. Semua hak cipta dilindungi.</span>
            <span>{{ $tagline }}</span>
        </div>
    </div>
    </div>
</footer>
