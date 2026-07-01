@php
    $settings = app(\App\Services\SiteSettingService::class);
    $facilities = $settings->get('home_facilities');
    if (! is_array($facilities) || $facilities === []) {
        $facilities = \App\Support\SiteContent::facilities();
    }
@endphp
<section class="bg-white py-10 md:py-14">
    <div class="container-page">
        <div class="text-center max-w-xl mx-auto mb-8">
            <p class="section-eyebrow">{{ $settings->get('home_facilities_eyebrow', \App\Support\SiteContent::text('home_facilities_eyebrow')) }}</p>
            <h2 class="section-heading mt-2">{{ $settings->get('home_facilities_heading', \App\Support\SiteContent::text('home_facilities_heading')) }}</h2>
            <p class="text-slate-600 mt-2">{{ $settings->get('home_facilities_subheading', \App\Support\SiteContent::text('home_facilities_subheading')) }}</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($facilities as $f)
                <div class="group flex flex-col items-center gap-3 rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft p-5 text-center transition hover:-translate-y-1 hover:shadow-premium">
                    <div class="h-12 w-12 rounded-xl bg-brand-50 text-brand-600 grid place-items-center transition group-hover:bg-brand-600 group-hover:text-white">
                        <iconify-icon icon="{{ \App\Support\UiIcons::iconify($f['icon'] ?? null) }}" class="text-2xl"></iconify-icon>
                    </div>
                    <p class="text-sm font-semibold text-slate-800">{{ $f['name'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
