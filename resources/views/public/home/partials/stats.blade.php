@php
    $stats = [
        ['icon' => 'ph:stethoscope-duotone', 'value' => (int) ($totalDoctors ?? 0), 'label' => 'Dokter', 'bg' => 'bg-sky-50', 'tx' => 'text-sky-600'],
        ['icon' => 'ph:hospital-duotone', 'value' => (int) ($totalPolyclinics ?? 0), 'label' => 'Poliklinik', 'bg' => 'bg-emerald-50', 'tx' => 'text-emerald-600'],
        ['icon' => 'ph:first-aid-kit-duotone', 'value' => (int) ($totalServices ?? 0), 'label' => 'Layanan', 'bg' => 'bg-amber-50', 'tx' => 'text-amber-600'],
        ['icon' => 'ph:siren-duotone', 'value' => '24 Jam', 'label' => 'IGD Siaga', 'bg' => 'bg-rose-50', 'tx' => 'text-rose-600'],
    ];
@endphp
<section class="bg-white py-8 md:py-10">
    <div class="container-page">
        <div class="grid grid-cols-2 md:grid-cols-4 overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5 divide-y divide-slate-100 sm:divide-y-0 sm:divide-x">
            @foreach ($stats as $s)
                <div class="flex items-center gap-3 p-4 md:p-5">
                    <div class="h-11 w-11 rounded-xl {{ $s['bg'] }} {{ $s['tx'] }} grid place-items-center shrink-0">
                        <iconify-icon icon="{{ $s['icon'] }}" class="text-2xl"></iconify-icon>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display text-xl md:text-2xl font-bold text-slate-900 leading-none">{{ $s['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $s['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
