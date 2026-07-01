@php
    $settings = app(\App\Services\SiteSettingService::class);
    $actions = $settings->get('home_quick_actions');
    if (! is_array($actions) || $actions === []) {
        $actions = \App\Support\SiteContent::quickActions();
    }

    $tones = [
        ['chip' => 'bg-sky-100 text-sky-700', 'hover' => 'group-hover:bg-sky-600', 'ring' => 'hover:border-sky-200', 'arrow' => 'text-sky-500'],
        ['chip' => 'bg-emerald-100 text-emerald-700', 'hover' => 'group-hover:bg-emerald-600', 'ring' => 'hover:border-emerald-200', 'arrow' => 'text-emerald-500'],
        ['chip' => 'bg-amber-100 text-amber-700', 'hover' => 'group-hover:bg-amber-500', 'ring' => 'hover:border-amber-200', 'arrow' => 'text-amber-500'],
        ['chip' => 'bg-rose-100 text-rose-700', 'hover' => 'group-hover:bg-rose-600', 'ring' => 'hover:border-rose-200', 'arrow' => 'text-rose-500'],
        ['chip' => 'bg-violet-100 text-violet-700', 'hover' => 'group-hover:bg-violet-600', 'ring' => 'hover:border-violet-200', 'arrow' => 'text-violet-500'],
    ];
@endphp
<section class="bg-white">
    <div class="container-page pb-8 md:pb-10">
        <div class="flex flex-wrap gap-4">
            @foreach ($actions as $a)
                @php
                    $url = $a['url'] ?? '#';
                    $external = \Illuminate\Support\Str::startsWith($url, 'http');
                    $t = $tones[$loop->index % count($tones)];
                @endphp
                <a href="{{ $url }}" @if ($external) target="_blank" rel="noopener" @endif
                   class="group relative flex-1 min-w-[220px] flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:shadow-premium {{ $t['ring'] }}">
                    <div class="h-14 w-14 rounded-2xl {{ $t['chip'] }} grid place-items-center shrink-0 transition group-hover:text-white {{ $t['hover'] }}">
                        <iconify-icon icon="{{ \App\Support\UiIcons::iconify($a['icon'] ?? null) }}" class="text-3xl"></iconify-icon>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-display font-bold text-slate-900">{{ $a['label'] ?? '' }}</p>
                        <p class="text-sm text-slate-500 mt-0.5 line-clamp-1">{{ $a['description'] ?? '' }}</p>
                    </div>
                    <svg class="h-5 w-5 shrink-0 {{ $t['arrow'] }} transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </div>
</section>
