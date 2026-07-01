@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Alur Pendaftaran',
    'breadcrumbs' => [['Pendaftaran', null]],
])

<section class="container-page py-10 max-w-4xl">
    @if ($safeBody)
        <article class="prose-rsud">{!! $safeBody !!}</article>
    @else
        <p class="text-slate-500">Konten alur pendaftaran belum disiapkan.</p>
    @endif
</section>
@endsection
