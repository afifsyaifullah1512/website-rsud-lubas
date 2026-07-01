{{-- Hero slider (Swiper carousel) beranda — Requirement 1.2, 35.1–35.10. --}}
@php
    $settings = app(\App\Services\SiteSettingService::class);
    $rsName = $settings->get('rs_name', 'RSUD');
    $address = $settings->get('address');
    $tagline = $settings->get('footer_tagline', \App\Support\SiteContent::text('footer_tagline'));

    $slides = collect($heroSlides ?? []);
    $fallback = $heroFallback ?? ['title' => null, 'subtitle' => null, 'image' => null];
    $multiple = $slides->count() > 1;

    $imageUrl = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, 'http')
            ? $path
            : asset('storage/'.$path);
    };
@endphp

<section aria-label="Sorotan utama" aria-roledescription="carousel" class="bg-brand-900">
    @if ($slides->isNotEmpty())
        {{-- Slider data-driven dari Hero_Slide aktif (Req 35.1, 35.2) --}}
        <div
            class="w-full"
            x-data="{
                swiper: null,
                init() {
                    if (! window.Swiper) {
                        return;
                    }
                    @if ($multiple)
                    // Hormati preferensi pengguna: nonaktifkan auto-play bila reduced-motion (Req 35 — aksesibilitas).
                    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    @endif
                    this.swiper = new window.Swiper(this.$refs.swiper, {
                        modules: Object.values(window.SwiperModules ?? {}),
                        loop: {{ $multiple ? 'true' : 'false' }},
                        speed: 650,
                        grabCursor: {{ $multiple ? 'true' : 'false' }},
                        @if ($multiple)
                        autoplay: reduceMotion ? false : { delay: 5000, pauseOnMouseEnter: true, disableOnInteraction: false },
                        navigation: {
                            nextEl: this.$refs.swiper.querySelector('.hero-nav--next'),
                            prevEl: this.$refs.swiper.querySelector('.hero-nav--prev'),
                        },
                        pagination: {
                            el: this.$refs.swiper.querySelector('.swiper-pagination'),
                            clickable: true,
                        },
                        keyboard: { enabled: true, onlyInViewport: true },
                        @endif
                        a11y: {
                            enabled: true,
                            prevSlideMessage: 'Slide sebelumnya',
                            nextSlideMessage: 'Slide berikutnya',
                            paginationBulletMessage: 'Ke slide @{{index}}',
                        },
                    });
                },
                pause() { this.swiper?.autoplay?.stop(); },
                resume() { this.swiper?.autoplay?.start(); },
            }"
            {{-- Pause auto-play saat fokus masuk; lanjut hanya bila fokus benar-benar keluar dari carousel (Req 35.6) --}}
            @focusin="pause()"
            @focusout="if (! $el.contains($event.relatedTarget)) resume()"
        >
            <div class="swiper hero-swiper" x-ref="swiper" @if ($multiple) tabindex="0" aria-label="Carousel sorotan utama, gunakan tombol panah untuk berpindah slide" @endif>
                <div class="swiper-wrapper">
                    @foreach ($slides as $i => $slide)
                        <div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Slide {{ $i + 1 }} dari {{ $slides->count() }}">
                            <div class="relative h-[460px] sm:h-[560px] lg:h-[640px]">
                                <img
                                    src="{{ $imageUrl($slide->image_path) }}"
                                    alt="{{ $slide->headline ?? $rsName }}"
                                    class="absolute inset-0 h-full w-full object-cover"
                                    @if ($i === 0)
                                        loading="eager" fetchpriority="high"
                                    @else
                                        loading="lazy"
                                    @endif
                                >
                                {{-- Overlay gradien untuk kontras teks >= 4.5:1 (Req 35.10) --}}
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-900/55 to-slate-900/10"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>

                                @if ($slide->headline || $slide->subheadline || ($slide->cta_label && $slide->cta_url))
                                    <div class="relative h-full container-page flex items-center px-12 sm:px-14 md:px-20 lg:px-24">
                                        <div class="max-w-2xl text-white">
                                            @if ($slide->headline)
                                                <h1 class="font-display text-3xl md:text-4xl xl:text-5xl font-bold leading-tight tracking-tight text-balance drop-shadow-sm">
                                                    {{ $slide->headline }}
                                                </h1>
                                            @endif
                                            @if ($slide->subheadline)
                                                <p class="mt-4 text-base md:text-lg text-white/90 max-w-xl text-pretty drop-shadow-sm">
                                                    {{ $slide->subheadline }}
                                                </p>
                                            @endif
                                            @if ($slide->cta_label && $slide->cta_url)
                                                <div class="mt-7">
                                                    <a href="{{ $slide->cta_url }}" class="btn-white">
                                                        {{ $slide->cta_label }}
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($multiple)
                    <div class="swiper-pagination"></div>
                    <button type="button" class="hero-nav hero-nav--prev" aria-label="Slide sebelumnya">
                        <svg class="h-5 w-5 md:h-6 md:w-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="hero-nav hero-nav--next" aria-label="Slide berikutnya">
                        <svg class="h-5 w-5 md:h-6 md:w-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif
            </div>
        </div>
    @else
        {{-- Fallback hero statis dari Site_Setting — area hero tidak pernah kosong (Req 35.4) --}}
        <div class="w-full">
            <div class="hero-static relative h-[460px] sm:h-[560px] lg:h-[640px]">
                <img
                    src="{{ $imageUrl($fallback['image']) ?? 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=1600&q=80&auto=format&fit=crop' }}"
                    alt="{{ $fallback['title'] ?? $rsName }}"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager" fetchpriority="high"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-900/55 to-slate-900/10"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>
                <div class="relative h-full container-page flex items-center px-12 sm:px-14 md:px-20 lg:px-24">
                    <div class="max-w-2xl text-white">
                        <p class="text-xs font-semibold uppercase tracking-widest text-white/80">{{ $tagline }}</p>
                        <h1 class="mt-3 font-display text-3xl md:text-4xl xl:text-5xl font-bold leading-tight tracking-tight text-balance drop-shadow-sm">
                            {{ $fallback['title'] ?? 'Selamat Datang di '.$rsName }}
                        </h1>
                        <p class="mt-4 text-base md:text-lg text-white/90 max-w-xl text-pretty drop-shadow-sm">
                            {{ $fallback['subtitle'] ?? 'Portal informasi resmi rumah sakit. Akses jadwal dokter, layanan, berita, dan kanal pengaduan publik.' }}
                        </p>
                        @if ($address)
                            <p class="mt-3 text-sm text-white/80 inline-flex items-start gap-2 max-w-xl">
                                <svg class="h-4 w-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1116 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>{{ $address }}</span>
                            </p>
                        @endif
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ route('jadwal') }}" class="btn-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                                Jadwal Dokter
                            </a>
                            <a href="{{ route('pendaftaran') }}" class="btn-outline">
                                Cara Pendaftaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
