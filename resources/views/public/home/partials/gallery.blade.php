@php
    $settings = app(\App\Services\SiteSettingService::class);
    $items = collect($galleries ?? []);
    $coverOf = static function ($g) {
        $first = $g->media->first();
        if (! $first) {
            return 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=800&q=80';
        }
        return \Illuminate\Support\Str::startsWith((string) $first->path, 'http') ? $first->path : asset('storage/'.$first->path);
    };
@endphp
@if ($items->isNotEmpty())
<section class="bg-white py-10 md:py-14">
    <div class="container-page">
        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <p class="section-eyebrow">{{ $settings->get('home_gallery_eyebrow', \App\Support\SiteContent::text('home_gallery_eyebrow')) }}</p>
                <h2 class="section-heading mt-2">{{ $settings->get('home_gallery_heading', \App\Support\SiteContent::text('home_gallery_heading')) }}</h2>
                <p class="text-slate-600 mt-1">{{ $settings->get('home_gallery_subheading', \App\Support\SiteContent::text('home_gallery_subheading')) }}</p>
            </div>
            <a href="{{ route('galeri') }}" class="hidden md:inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800 shrink-0">
                Semua galeri
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </a>
        </div>

        <div x-data="rowCarousel()" class="relative">
            <div class="swiper py-2" x-ref="swiper">
                <div class="swiper-wrapper">
                    @foreach ($items as $g)
                        <div class="swiper-slide h-auto">
                            <a href="{{ route('galeri.show', $g->slug) }}"
                               class="group relative block h-56 overflow-hidden rounded-2xl shadow-soft ring-1 ring-slate-900/5 transition duration-300 hover:-translate-y-1.5 hover:shadow-premium">
                                <img src="{{ $coverOf($g) }}" alt="{{ $g->title }}" loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/20 to-transparent"></div>
                                <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-slate-700 backdrop-blur">
                                    <iconify-icon icon="ph:images-duotone" class="text-sm text-brand-600"></iconify-icon>
                                    {{ $g->media->count() }}
                                </span>
                                <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                    <h3 class="font-display font-bold leading-snug line-clamp-2 drop-shadow-sm">{{ $g->title }}</h3>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-6 flex items-center justify-center gap-3 md:justify-end">
                <button type="button" x-ref="prev" aria-label="Sebelumnya" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" x-ref="next" aria-label="Berikutnya" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-soft transition hover:bg-brand-50 hover:text-brand-700 disabled:opacity-30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
@endif
