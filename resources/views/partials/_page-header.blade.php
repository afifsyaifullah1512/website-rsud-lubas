{{--
    Page header reusable untuk halaman interior.
    Variabel:
      $title       — wajib
      $description — opsional
      $breadcrumbs — opsional, array of [label, url|null]
--}}
<section class="bg-white border-b border-slate-200">
    <div class="container-page py-8 md:py-10">
        @if (! empty($breadcrumbs))
            <nav class="text-xs text-slate-500 mb-3 flex flex-wrap items-center gap-1.5">
                <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
                @foreach ($breadcrumbs as [$label, $url])
                    <span>/</span>
                    @if ($url)
                        <a href="{{ $url }}" class="hover:text-brand-700">{{ $label }}</a>
                    @else
                        <span class="text-slate-700">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="font-display text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        @if (! empty($description))
            <p class="mt-2 text-slate-600 max-w-3xl">{{ $description }}</p>
        @endif
    </div>
</section>
