@php
    $settings = app(\App\Services\SiteSettingService::class);
    $rsName = $settings->get('rs_name', 'RSUD');
    $rsDesc = $settings->get('rs_description', 'Rumah sakit umum daerah yang melayani masyarakat dengan sepenuh hati.');

    $aboutHeading = trim((string) $settings->get('home_about_heading', '')) ?: ('Tentang '.$rsName);
    $aboutText = trim((string) $settings->get('home_about_text', '')) ?: $rsDesc;

    $aboutImg = $settings->get('home_about_image');
    $aboutImg = $aboutImg
        ? (\Illuminate\Support\Str::startsWith($aboutImg, 'http') ? $aboutImg : asset('storage/'.$aboutImg))
        : 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=1000&q=80&auto=format&fit=crop';

    $highlights = $settings->get('home_about_highlights');
    if (! is_array($highlights) || $highlights === []) {
        $highlights = \App\Support\SiteContent::aboutHighlights();
    }
@endphp
<section class="bg-white py-10 md:py-14">
    <div class="container-page grid gap-10 lg:grid-cols-2 lg:items-center">
        {{-- Gambar --}}
        <div class="relative">
            <img src="{{ $aboutImg }}" alt="{{ $rsName }}"
                 class="w-full h-72 md:h-[420px] object-cover rounded-3xl shadow-soft ring-1 ring-slate-900/5">
        </div>

        {{-- Teks --}}
        <div class="lg:pl-4 mt-6 lg:mt-0">
            <p class="section-eyebrow">{{ $settings->get('home_about_eyebrow', \App\Support\SiteContent::text('home_about_eyebrow')) }}</p>
            <h2 class="section-heading mt-2">{{ $aboutHeading }}</h2>
            <p class="mt-4 text-slate-600 leading-relaxed whitespace-pre-line">{{ $aboutText }}</p>

            @if (! empty($highlights))
                <ul class="mt-6 space-y-3">
                    @foreach ($highlights as $h)
                        @php $ht = is_array($h) ? ($h['text'] ?? '') : (string) $h; @endphp
                        @if ($ht !== '')
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 h-6 w-6 rounded-full bg-brand-50 text-brand-600 grid place-items-center shrink-0">
                                    <iconify-icon icon="ph:check-duotone" class="text-base"></iconify-icon>
                                </span>
                                <span class="text-slate-700">{{ $ht }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('profil.sejarah') }}" class="btn-primary">
                    Profil Selengkapnya
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                </a>
                <a href="{{ route('profil.visi-misi') }}" class="btn-outline-brand">Visi & Misi</a>
            </div>
        </div>
    </div>
</section>
