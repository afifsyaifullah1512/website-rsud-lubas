<section class="container-page py-10 md:py-14">
    @php
        $settings = app(\App\Services\SiteSettingService::class);
        $newsHeading = $settings->get('home_news_heading', \App\Support\SiteContent::text('home_news_heading'));
        $newsSub = $settings->get('home_news_subheading', \App\Support\SiteContent::text('home_news_subheading'));
    @endphp
    <div class="flex items-end justify-between mb-6">
        <div>
            <h2 class="section-heading">{{ $newsHeading }}</h2>
            <p class="text-slate-600 mt-1">{{ $newsSub }}</p>
        </div>
        <a href="{{ route('berita.index') }}" class="hidden md:inline text-sm text-brand-700 hover:underline">Semua berita →</a>
    </div>

    @php($newsList = collect($latestNews ?? []))
    @if ($newsList->count() > 0)
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($newsList->take(6) as $n)
                <article class="card overflow-hidden hover:border-brand-200 transition">
                    <a href="{{ route('berita.show', $n->slug) }}" class="block">
                        @if ($n->cover_image)
                            <div class="aspect-video overflow-hidden bg-slate-100">
                                <img loading="lazy" src="{{ str_starts_with($n->cover_image, 'http') ? $n->cover_image : asset('storage/'.$n->cover_image) }}" alt="{{ $n->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                @if ($n->category)
                                    <span class="font-medium text-brand-700">{{ $n->category->name }}</span>
                                    <span>·</span>
                                @endif
                                <time>{{ optional($n->published_at)->translatedFormat('d M Y') }}</time>
                            </div>
                            <h3 class="font-semibold mt-1.5 text-slate-900 line-clamp-2 leading-snug">{{ $n->title }}</h3>
                            <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $n->excerpt }}</p>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    @else
        <p class="text-slate-500">Belum ada berita dipublikasikan.</p>
    @endif
</section>
