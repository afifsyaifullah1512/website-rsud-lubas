@extends('layouts.public')

@section('content')
    @php
        $cfg = app(\App\Services\SiteSettingService::class);
        $showQuick = (bool) $cfg->get('home_show_quick_actions', true);
        $showTrust = (bool) $cfg->get('home_show_trust', true);
    @endphp
    @include('public.home.partials.hero')
    @include('public.home.partials.stats')
    @if ($showQuick)
        @include('public.home.partials.quick-actions')
    @endif
    @if ($showTrust)
        @include('public.home.partials.trust')
    @endif
    @include('public.home.partials.about')
    @include('public.home.partials.services')
    @include('public.home.partials.facilities')
    @include('public.home.partials.schedule-today')
    @include('public.home.partials.gallery')
    @include('public.home.partials.news')
    @include('public.home.partials.cta-pengaduan')
    @include('public.home.partials.contact-cta')
@endsection
