@php
    /** @var \App\Services\SiteSettingService $settings */
    $settings = app(\App\Services\SiteSettingService::class);
    $rsName = $settings->get('rs_name', 'RSUD');
    $logo = $settings->get('logo');
    $phone = $settings->get('phone');
    $email = $settings->get('email');
    $hours = $settings->get('operational_hours');
    // Kontak gawat darurat: pakai key khusus bila ada, jatuh balik ke telepon utama.
    $emergencyPhone = $settings->get('emergency_phone', $phone);
    // Tampilkan badge IGD 24 Jam? Dapat dinonaktifkan dari admin.
    $igdActive = (bool) $settings->get('igd_active', true);
    // Toggle menu/link header PPID & Karir.
    $ppidActive = (bool) $settings->get('ppid_active', true);
    $karirActive = (bool) $settings->get('karir_active', true);
    // Subjudul header (di bawah nama RS). Default nonaktif (kosong = disembunyikan).
    $headerSubtitle = (string) $settings->get('header_subtitle', '');
    // URL pendaftaran online eksternal (RegOnline). Dapat diubah lewat admin (SiteSetting).
    $registrationUrl = $settings->get('registration_url', 'https://rsud.agamkab.go.id/apps/RegOnline/');
    $facebook = $settings->get('social_facebook');
    $instagram = $settings->get('social_instagram');
    $youtube = $settings->get('social_youtube');
    $telHref = static fn (?string $n): string => $n ? 'tel:'.preg_replace('/[^0-9+]/', '', $n) : '#';

    /** @var \Illuminate\Support\Collection $navItems */
    $navItems = app(\App\Services\NavMenuService::class)->tree();

    if ($navItems->isEmpty()) {
        $navItems = collect([
            ['id' => 0, 'label' => 'Beranda', 'url' => route('home'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'Profil', 'url' => '#', 'opens_new_tab' => false, 'children' => [
                ['id' => 0, 'label' => 'Sejarah', 'url' => route('profil.sejarah'), 'opens_new_tab' => false],
                ['id' => 0, 'label' => 'Visi & Misi', 'url' => route('profil.visi-misi'), 'opens_new_tab' => false],
                ['id' => 0, 'label' => 'Struktur Organisasi', 'url' => route('profil.struktur'), 'opens_new_tab' => false],
                ['id' => 0, 'label' => 'Sambutan Direktur', 'url' => route('profil.direktur'), 'opens_new_tab' => false],
            ]],
            ['id' => 0, 'label' => 'Layanan', 'url' => route('layanan.index'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'Jadwal Dokter', 'url' => route('jadwal'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'Berita', 'url' => route('berita.index'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'Galeri', 'url' => route('galeri'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'PPID', 'url' => route('ppid.index'), 'opens_new_tab' => false, 'children' => []],
            ['id' => 0, 'label' => 'Kontak', 'url' => route('kontak'), 'opens_new_tab' => false, 'children' => []],
        ]);
    }

    // Sembunyikan PPID/Karir dari navigasi bila dinonaktifkan admin.
    $navItems = $navItems->reject(function ($item) use ($ppidActive, $karirActive) {
        $label = strtolower(trim((string) ($item['label'] ?? '')));
        if (! $ppidActive && $label === 'ppid') {
            return true;
        }
        if (! $karirActive && $label === 'karir') {
            return true;
        }

        return false;
    })->values();

    $currentPath = '/'.trim(request()->path(), '/');
    $isActive = function (array $item) use ($currentPath): bool {
        $url = $item['url'] ?? '';
        if ($url === '#' || $url === '') return false;
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = '/'.trim($path, '/');
        if ($path === '/') return $currentPath === '/';
        return str_starts_with($currentPath, $path);
    };
@endphp

{{-- Top bar: kontak ringkas, jam layanan, IGD 24 jam, quick links + sosial (Req 1.8) --}}
<div class="hidden md:block bg-gradient-to-br from-brand-800 via-brand-800 to-brand-950 text-brand-50 text-xs">
    <div class="container-page py-2 flex items-center justify-between gap-4">
        <div class="flex items-center gap-5 min-w-0">
            {{-- IGD 24 Jam — kontak gawat darurat, paling kiri (bisa dinonaktifkan) --}}
            @if ($igdActive && $emergencyPhone)
                <a href="{{ $telHref($emergencyPhone) }}" class="inline-flex items-center gap-1.5 rounded-full bg-rose-600/90 hover:bg-rose-600 text-white px-3 py-1 font-semibold transition shrink-0">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-rose-200 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                    </span>
                    IGD 24 Jam: {{ $emergencyPhone }}
                </a>
            @endif
            @if ($phone)
                <a href="{{ $telHref($phone) }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                    <iconify-icon icon="ph:phone-call-duotone" class="text-base"></iconify-icon>
                    {{ $phone }}
                </a>
            @endif
            @if ($email)
                <a href="mailto:{{ $email }}" class="hidden lg:inline-flex items-center gap-1.5 hover:text-white transition">
                    <iconify-icon icon="ph:envelope-simple-duotone" class="text-base"></iconify-icon>
                    {{ $email }}
                </a>
            @endif
        </div>

        <div class="flex items-center gap-4 shrink-0">
            {{-- Quick links --}}
            <span class="hidden lg:flex items-center gap-3 text-brand-100/90">
                @if ($ppidActive)
                    <a href="{{ route('ppid.index') }}" class="hover:text-white transition">PPID</a>
                    <span class="text-brand-700">|</span>
                @endif
                <a href="{{ route('pengaduan.create') }}" class="hover:text-white transition">Pengaduan</a>
            </span>

            {{-- Sosial media --}}
            @if ($facebook || $instagram || $youtube)
                <span class="hidden lg:flex items-center gap-2.5 text-brand-100/80">
                    @if ($facebook)
                        <a href="{{ $facebook }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="Facebook">
                            <iconify-icon icon="ph:facebook-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                    @if ($instagram)
                        <a href="{{ $instagram }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="Instagram">
                            <iconify-icon icon="ph:instagram-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                    @if ($youtube)
                        <a href="{{ $youtube }}" target="_blank" rel="noopener" class="hover:text-white transition" aria-label="YouTube">
                            <iconify-icon icon="ph:youtube-logo-duotone" class="text-xl"></iconify-icon>
                        </a>
                    @endif
                </span>
            @endif
        </div>
    </div>
</div>

{{-- Main navigation --}}
<header
    class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200 transition-shadow duration-300"
    x-data="{ mobile: false, scrolled: false }"
    @scroll.window="scrolled = window.scrollY > 8"
    :class="scrolled ? 'shadow-lg shadow-slate-900/5' : ''">
    <div class="container-page">
        <div class="flex items-center justify-between h-16 lg:h-20 gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                @if ($logo)
                    <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/'.$logo) }}" alt="{{ $rsName }}" class="h-12 w-12 lg:h-14 lg:w-14 object-contain">
                @else
                    <div class="h-12 w-12 lg:h-14 lg:w-14 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white grid place-items-center shadow-soft">
                        <svg class="h-6 w-6 lg:h-7 lg:w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                @endif
                <div class="leading-tight">
                    <p class="font-display font-bold text-slate-900 text-[15px]">{{ $rsName }}</p>
                    @if (trim($headerSubtitle) !== '')
                        <p class="text-[11px] text-slate-500 hidden sm:block">{{ $headerSubtitle }}</p>
                    @endif
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-0.5 text-sm font-semibold text-slate-700">
                @foreach ($navItems as $item)
                    @php
                        $hasChildren = ! empty($item['children']);
                        $active = $isActive($item) || ($hasChildren && collect($item['children'])->contains(fn ($c) => $isActive($c)));
                    @endphp
                    @if ($hasChildren)
                        <div class="relative"
                             x-data="{ open: false, timer: null }"
                             @mouseenter="clearTimeout(timer); open = true"
                             @mouseleave="timer = setTimeout(() => open = false, 150)"
                             @click.outside="open = false"
                             @keydown.escape.window="open = false">
                            <button
                                type="button"
                                @click="open = ! open"
                                class="relative flex items-center gap-0.5 px-2 py-2 rounded-md hover:text-brand-700 {{ $active ? 'text-brand-700 after:absolute after:-bottom-0.5 after:left-2 after:right-2 after:h-0.5 after:rounded-full after:bg-brand-600' : '' }}"
                                :aria-expanded="open ? 'true' : 'false'">
                                {{ $item['label'] }}
                                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <template x-if="open">
                                <div class="absolute left-0 top-full pt-1 w-56 z-20">
                                    <div class="bg-white border border-slate-200 rounded-lg shadow-lg py-1">
                                        @foreach ($item['children'] as $child)
                                            <a href="{{ $child['url'] }}"
                                               @if ($child['opens_new_tab']) target="_blank" rel="noopener" @endif
                                               class="block px-4 py-1.5 text-sm leading-5 {{ $isActive($child) ? 'text-brand-700 bg-brand-50' : 'text-slate-700 hover:bg-slate-50' }}">
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}"
                           @if ($item['opens_new_tab']) target="_blank" rel="noopener" @endif
                           class="relative px-2 py-2 rounded-md hover:text-brand-700 {{ $active ? 'text-brand-700 after:absolute after:-bottom-0.5 after:left-2 after:right-2 after:h-0.5 after:rounded-full after:bg-brand-600' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100 hover:border-brand-300">
                    <iconify-icon icon="ph:clipboard-text-duotone" class="text-base"></iconify-icon>
                    Pendaftaran Online
                </a>
            </div>

            <button class="lg:hidden p-2 text-slate-700" @click="mobile = !mobile" :aria-expanded="mobile ? 'true' : 'false'" aria-controls="mobile-menu" aria-label="Buka menu navigasi">
                <svg x-show="!mobile" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="mobile" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="mobile-menu" x-show="mobile" x-cloak @keydown.escape.window="mobile = false" class="lg:hidden border-t border-slate-100 py-3 grid gap-0.5 text-sm font-medium">
            @foreach ($navItems as $item)
                @if (! empty($item['children']))
                    <details class="group">
                        <summary class="cursor-pointer list-none px-3 py-2 rounded hover:bg-slate-50 flex items-center justify-between">
                            <span>{{ $item['label'] }}</span>
                            <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="pl-4">
                            @foreach ($item['children'] as $child)
                                <a href="{{ $child['url'] }}"
                                   @if ($child['opens_new_tab']) target="_blank" rel="noopener" @endif
                                   class="block px-3 py-2 rounded hover:bg-slate-50 text-slate-600">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </details>
                @else
                    <a href="{{ $item['url'] }}"
                       @if ($item['opens_new_tab']) target="_blank" rel="noopener" @endif
                       class="block px-3 py-2 rounded hover:bg-slate-50">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
            <div class="mt-2 pt-3 border-t border-slate-100 grid gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="btn-primary text-sm flex-1 justify-center">
                        Pendaftaran Online
                        <svg class="h-3.5 w-3.5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
                    </a>
                </div>
                @if ($igdActive && $emergencyPhone)
                    <a href="{{ $telHref($emergencyPhone) }}" class="flex items-center justify-center gap-2 rounded-lg bg-rose-600 text-white px-3 py-2 text-sm font-semibold">
                        <span class="h-2 w-2 rounded-full bg-white"></span> IGD 24 Jam: {{ $emergencyPhone }}
                    </a>
                @endif
                <div class="px-1 grid gap-1 text-xs text-slate-500">
                    @if ($phone)
                        <a href="{{ $telHref($phone) }}" class="inline-flex items-center gap-2 hover:text-brand-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
