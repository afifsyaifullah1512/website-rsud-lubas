@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Galeri',
    'description' => 'Dokumentasi kegiatan dan fasilitas rumah sakit.',
    'breadcrumbs' => [['Galeri', null]],
])

<section class="container-page py-10 space-y-8">
    @forelse ($galleries as $type => $items)
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-3">{{ \App\Support\Enums\GalleryType::tryFrom((string) $type)?->label() ?? ucwords(strtolower((string) $type)) }}</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $g)
                    <a href="{{ route('galeri.show', $g->slug) }}" class="card overflow-hidden hover:border-brand-200 transition">
                        @if ($g->media->isNotEmpty())
                            @php($first = $g->media->first())
                            @php($firstUrl = str_starts_with((string) $first->path, 'http') ? $first->path : asset('storage/'.$first->path))
                            <div class="aspect-video bg-slate-100">
                                <img loading="lazy" src="{{ $firstUrl }}" alt="{{ $g->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="p-3">
                            <h3 class="font-medium text-sm text-slate-900">{{ $g->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-slate-500">Belum ada galeri.</p>
    @endforelse
</section>
@endsection
