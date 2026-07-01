{{-- Meta tags publik — Requirement 27.3 (SEO/OG). --}}
@php
    /** @var \App\Services\SiteSettingService $settings */
    $settings = app(\App\Services\SiteSettingService::class);
    $rsName = $settings->get('rs_name', config('app.name', 'RSUD'));
    $rsDescription = $settings->get('rs_description', 'Website resmi RSUD — informasi layanan, jadwal dokter, berita, dan kanal pengaduan.');

    // Sumber metadata (prioritas): @section('meta_*') > variabel ($page*) > SiteSetting.
    $metaTitle = trim($__env->yieldContent('meta_title')) ?: ($pageTitle ?? null);
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: ($pageDescription ?? null);
    $metaImage = trim($__env->yieldContent('meta_image')) ?: ($pageImage ?? null);

    $title = $metaTitle ? $metaTitle.' — '.$rsName : $rsName;
    $description = $metaDescription ?: $rsDescription;
    $image = $metaImage ?: $settings->get('og_image', asset('favicon.ico'));
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $rsName }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
