@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $page->title ?: $pageTitle,
    'breadcrumbs' => [[$page->title ?: $pageTitle, null]],
])

<section class="container-page py-10 max-w-4xl">
    <article class="prose-rsud">
        {!! $safeBody !!}
    </article>
</section>
@endsection
