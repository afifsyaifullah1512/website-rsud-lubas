@php
    use App\Support\Enums\ServiceType;

    /** @var \App\Models\Service $service */
    $type = $service->type instanceof ServiceType ? $service->type : ServiceType::tryFrom((string) $service->type);

    $img = $service->image
        ? (\Illuminate\Support\Str::startsWith($service->image, 'http') ? $service->image : asset('storage/'.$service->image))
        : ($type?->fallbackImage() ?? 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=900&q=80&auto=format&fit=crop');

    $chipText = match ($type?->tone()) {
        'rose' => 'text-rose-700', 'sky' => 'text-sky-700', 'amber' => 'text-amber-700',
        'violet' => 'text-violet-700', default => 'text-brand-700',
    };
@endphp

<a href="{{ route('layanan.show', $service->slug) }}"
   class="group relative block h-64 overflow-hidden rounded-3xl shadow-soft ring-1 ring-slate-900/5 transition duration-300 hover:-translate-y-1.5 hover:shadow-premium">
    <img src="{{ $img }}" alt="{{ $service->name }}" loading="lazy"
         class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/35 to-slate-900/0"></div>

    @if ($type)
        <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold {{ $chipText }} shadow-sm backdrop-blur">
            {{ $type->shortLabel() }}
        </span>
    @endif

    <div class="absolute inset-x-0 bottom-0 p-5 text-white">
        @if ($service->polyclinic)
            <p class="text-[11px] font-medium uppercase tracking-wider text-white/70">{{ $service->polyclinic->name }}</p>
        @endif
        <h3 class="font-display text-lg font-bold leading-snug drop-shadow-sm line-clamp-2">{{ $service->name }}</h3>
        <p class="mt-1 text-xs text-white/80 line-clamp-2">{{ $service->description }}</p>
        <span class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-white">
            Lihat detail
            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
        </span>
    </div>
</a>
