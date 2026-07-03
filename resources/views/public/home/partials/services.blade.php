@php
    $imageUrl = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, 'http') ? $path : asset('storage/'.$path);
    };
    $fallbackImg = 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&q=80&auto=format&fit=crop';
    $services = collect($featuredServices ?? []);
    $settings = app(\App\Services\SiteSettingService::class);
    $svcEyebrow = $settings->get('home_services_eyebrow', \App\Support\SiteContent::text('home_services_eyebrow'));
    $svcHeading = $settings->get('home_services_heading', \App\Support\SiteContent::text('home_services_heading'));
    $svcSub = $settings->get('home_services_subheading', \App\Support\SiteContent::text('home_services_subheading'));
@endphp

<section class="bg-brand-50 py-10 md:py-14">
    <div class="container-page">
        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <p class="section-eyebrow">{{ $svcEyebrow }}</p>
                <h2 class="section-heading mt-2">{{ $svcHeading }}</h2>
                <p class="text-slate-600 mt-2 max-w-xl">{{ $svcSub }}</p>
            </div>
            <a href="{{ route('layanan.index') }}" class="hidden md:inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800 shrink-0">
                Lihat semua
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </a>
        </div>

        @if ($services->isNotEmpty())
            <div
                x-data="{
                    swiper: null,
                    init() {
                        if (! window.Swiper) { return; }
                        this.swiper = new window.Swiper(this.$refs.swiper, {
                            modules: Object.values(window.SwiperModules ?? {}),
                            slidesPerView: 1.15,
                            spaceBetween: 20,
                            grabCursor: true,
                            navigation: {
                                nextEl: this.$refs.next,
                                prevEl: this.$refs.prev,
                            },
                            breakpoints: {
                                640: { slidesPerView: 2, spaceBetween: 24 },
                                1024: { slidesPerView: 3, spaceBetween: 24 },
                            },
                        });
                    },
                }"
                class="relative"
            >
                <div class="swiper services-swiper !pb-2" x-ref="swiper">
                    <div class="swiper-wrapper">
                        @foreach ($services as $service)
                            <div class="swiper-slide h-auto">
                                @include('public.service._card', ['service' => $service])
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol geser --}}
                <div class="mt-6 flex items-center justify-center gap-3 md:justify-end">
                    <button type="button" x-ref="prev" aria-label="Layanan sebelumnya"
                            class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-40">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" x-ref="next" aria-label="Layanan berikutnya"
                            class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-40">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        @else
            <p class="text-slate-500">Belum ada layanan unggulan.</p>
        @endif

        <div class="mt-8 text-center md:hidden">
            <a href="{{ route('layanan.index') }}" class="btn-ghost text-sm border border-slate-200">Lihat semua layanan</a>
        </div>
    </div>
</section>
