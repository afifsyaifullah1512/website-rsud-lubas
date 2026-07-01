@php
    $settings = app(\App\Services\SiteSettingService::class);
    $values = $settings->get('home_trust_badges');
    if (! is_array($values) || $values === []) {
        $values = \App\Support\SiteContent::trustBadges();
    }
    $tones = [
        ['bg' => 'bg-sky-100', 'txt' => 'text-sky-600'],
        ['bg' => 'bg-emerald-100', 'txt' => 'text-emerald-600'],
        ['bg' => 'bg-amber-100', 'txt' => 'text-amber-600'],
        ['bg' => 'bg-rose-100', 'txt' => 'text-rose-600'],
        ['bg' => 'bg-violet-100', 'txt' => 'text-violet-600'],
    ];
@endphp
<section class="bg-gradient-to-b from-white to-brand-50/40 border-y border-brand-100">
    <div class="container-page py-10 md:py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 items-start text-center">
            @foreach ($values as $v)
                @php $t = $tones[$loop->index % count($tones)]; @endphp
                <div class="flex flex-col items-center gap-3">
                    <div class="h-16 w-16 rounded-2xl {{ empty($v['image']) ? $t['bg'] : 'bg-white border border-slate-100' }} grid place-items-center shadow-soft overflow-hidden p-2">
                        @if (! empty($v['image']))
                            <img src="{{ \Illuminate\Support\Str::startsWith($v['image'], 'http') ? $v['image'] : asset('storage/'.$v['image']) }}" alt="{{ $v['label'] ?? '' }}" class="h-full w-full object-contain">
                        @else
                            <iconify-icon icon="{{ \App\Support\UiIcons::iconify($v['icon'] ?? null) }}" class="text-3xl {{ $t['txt'] }}" aria-hidden="true"></iconify-icon>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-slate-700">{{ $v['label'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
