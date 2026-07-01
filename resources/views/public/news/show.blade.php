@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $news->title,
    'breadcrumbs' => [
        ['Berita', route('berita.index')],
        [$news->category?->name ?? 'Detail', $news->category ? route('berita.kategori', $news->category->slug) : null],
    ],
])

<article class="container-page py-10">
    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 max-w-3xl">
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mb-4">
                @if ($news->category)
                    <span class="font-medium text-brand-700">{{ $news->category->name }}</span>
                    <span>·</span>
                @endif
                <time>{{ optional($news->published_at)->translatedFormat('d F Y') }}</time>
                @if ($news->author)
                    <span>·</span>
                    <span>{{ $news->author->name }}</span>
                @endif
                <span>·</span>
                <span>{{ $news->views }}× dibaca</span>
            </div>

            @if ($news->cover_image)
                <div class="rounded-md overflow-hidden mb-6 bg-slate-100">
                    <img src="{{ str_starts_with($news->cover_image, 'http') ? $news->cover_image : asset('storage/'.$news->cover_image) }}" alt="{{ $news->title }}" class="w-full aspect-video object-cover">
                </div>
            @endif

            <div class="prose-rsud">
                {!! $safeBody ?? $news->body !!}
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="sticky top-24">
                <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-3">Berita Lainnya</h2>
                @if (isset($related) && $related->isNotEmpty())
                    <ul class="space-y-4">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('berita.show', $item->slug) }}" class="flex gap-3 group">
                                    @if ($item->cover_image)
                                        <img loading="lazy" src="{{ str_starts_with($item->cover_image, 'http') ? $item->cover_image : asset('storage/'.$item->cover_image) }}" alt="" class="h-14 w-20 flex-none rounded object-cover bg-slate-100">
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-slate-900 group-hover:text-brand-700 line-clamp-2">{{ $item->title }}</span>
                                        <time class="block text-xs text-slate-500 mt-0.5">{{ optional($item->published_at)->translatedFormat('d M Y') }}</time>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">Belum ada berita lain.</p>
                @endif
            </div>
        </aside>
    </div>
</article>
@endsection
