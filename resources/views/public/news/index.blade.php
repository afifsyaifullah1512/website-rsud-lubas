@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Berita & Pengumuman',
    'description' => 'Informasi terbaru seputar layanan dan kegiatan rumah sakit.',
    'breadcrumbs' => [['Berita', null]],
])

<section class="container-page py-10">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('berita.index') }}" class="px-3.5 py-1.5 rounded-full font-medium transition @if(! isset($currentCategory)) bg-brand-600 text-white @else border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 @endif">Semua</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('berita.kategori', $cat->slug) }}" class="px-3.5 py-1.5 rounded-full font-medium transition @if(isset($currentCategory) && $currentCategory->id === $cat->id) bg-brand-600 text-white @else border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 @endif">{{ $cat->name }}</a>
                @endforeach
            </div>
        @endif

        @livewire('news-search')
    </div>

    @if ($news->total() > 0)
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($news as $n)
                <article class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-premium">
                    <a href="{{ route('berita.show', $n->slug) }}" class="flex flex-1 flex-col">
                        <div class="aspect-video overflow-hidden bg-slate-100">
                            @if ($n->cover_image)
                                <img loading="lazy" src="{{ str_starts_with($n->cover_image, 'http') ? $n->cover_image : asset('storage/'.$n->cover_image) }}" alt="{{ $n->title }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-full grid place-items-center text-brand-200">
                                    <iconify-icon icon="ph:newspaper-duotone" class="text-5xl"></iconify-icon>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                @if ($n->category)
                                    <span class="font-semibold text-brand-700">{{ $n->category->name }}</span>
                                    <span>·</span>
                                @endif
                                <time>{{ optional($n->published_at)->translatedFormat('d M Y') }}</time>
                            </div>
                            <h3 class="font-display font-bold mt-1.5 text-slate-900 line-clamp-2 leading-snug group-hover:text-brand-700 transition">{{ $n->title }}</h3>
                            <p class="text-sm text-slate-600 mt-1 line-clamp-2 flex-1">{{ $n->excerpt }}</p>
                            <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-700">
                                Baca selengkapnya
                                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                            </span>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $news->withQueryString()->links() }}</div>
    @else
        <p class="text-slate-500">Tidak ada berita ditemukan.</p>
    @endif
</section>
@endsection
