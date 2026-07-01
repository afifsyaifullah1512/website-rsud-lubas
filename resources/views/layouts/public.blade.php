<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials._meta-tags')
    {{-- Font: Inter (teks) + Plus Jakarta Sans (judul) via Bunny Fonts (privacy-friendly) --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&plus-jakarta-sans:500,600,700,800&display=swap">
    {{-- Iconify — ikon profesional (Material Symbols) dipakai di kartu/ikon UI --}}
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    {{-- Hide [x-cloak] elements before main CSS loads (anti-FOUC). --}}
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials._theme-colors')
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 bg-brand-600 text-white px-3 py-1 rounded">Lewati ke konten</a>

    @include('partials._navbar')

    <main id="content" class="flex-1">
        @if (session('status'))
            <div class="container mx-auto px-4 lg:px-6 mt-4">
                <div class="bg-brand-50 border border-brand-200 text-brand-800 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials._footer')

    @stack('scripts')
</body>
</html>
