@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $gallery->title,
    'description' => $gallery->description,
    'breadcrumbs' => [
        ['Galeri', route('galeri')],
        [$gallery->title, null],
    ],
])

<section class="container-page py-10">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($gallery->media as $m)
            @php($mediaUrl = str_starts_with((string) $m->path, 'http') ? $m->path : asset('storage/'.$m->path))
            <figure class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft transition hover:-translate-y-1 hover:shadow-premium">
                <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                    <img loading="lazy" src="{{ $mediaUrl }}" alt="{{ $m->caption ?? $gallery->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                </div>
                @if ($m->caption)
                    <figcaption class="flex items-start gap-2 border-t border-slate-100 bg-slate-50/70 px-4 py-3 text-sm text-slate-600">
                        <iconify-icon icon="ph:image-duotone" class="mt-0.5 shrink-0 text-base text-brand-500"></iconify-icon>
                        <span>{{ $m->caption }}</span>
                    </figcaption>
                @endif
            </figure>
        @endforeach
    </div>
</section>
@endsection
