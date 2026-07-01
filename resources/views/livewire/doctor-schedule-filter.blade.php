<div>
    {{-- Filter: wire:model.live memperbarui daftar tanpa reload (Req 4.2). --}}
    <form wire:submit.prevent class="rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft p-4 md:p-5 grid gap-3 md:grid-cols-4 mb-8">
        <div>
            <label for="filter-polyclinic" class="block text-xs font-semibold text-slate-600 mb-1.5">Poliklinik</label>
            <select id="filter-polyclinic" wire:model.live="polyclinicId" class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                <option value="">Semua Poliklinik</option>
                @foreach ($polyclinics as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter-day" class="block text-xs font-semibold text-slate-600 mb-1.5">Hari</label>
            <select id="filter-day" wire:model.live="day" class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                <option value="">Semua Hari</option>
                @foreach ($days as $d)
                    <option value="{{ $d->value }}">{{ $d->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter-q" class="block text-xs font-semibold text-slate-600 mb-1.5">Cari</label>
            <div class="relative">
                <iconify-icon icon="ph:magnifying-glass-duotone" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-400"></iconify-icon>
                <input id="filter-q" type="text" wire:model.live.debounce.300ms="q" minlength="2"
                    placeholder="Nama dokter / spesialisasi" class="w-full rounded-xl border-slate-200 pl-9 text-sm focus:border-brand-400 focus:ring-brand-400">
            </div>
        </div>
        <div class="flex items-end gap-2">
            <button type="button" wire:click="resetFilters"
                class="btn-outline-brand btn-sm">Reset</button>
            <span wire:loading class="self-center text-xs text-slate-400">Memuat…</span>
        </div>
    </form>

    <div wire:loading.class="opacity-50 transition-opacity">
        @php $byDoctor = collect($schedules)->groupBy('doctorId'); @endphp
        @if ($byDoctor->isNotEmpty())
            <div class="mb-4 text-sm text-slate-500">{{ $byDoctor->count() }} dokter ditemukan</div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($byDoctor as $docId => $rows)
                    @php $d = $rows->first(); @endphp
                    <div wire:key="doc-{{ $docId }}"
                         class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-premium">
                        {{-- Header dokter --}}
                        <div class="flex items-center gap-3 p-5 pb-4">
                            @if ($d->doctorPhotoUrl)
                                <img src="{{ $d->doctorPhotoUrl }}" alt="{{ $d->doctorName }}" loading="lazy"
                                     class="h-16 w-16 rounded-2xl object-cover bg-brand-50 ring-2 ring-brand-100 shrink-0">
                            @else
                                <div class="h-16 w-16 rounded-2xl bg-brand-100 text-brand-700 grid place-items-center font-display text-xl font-bold ring-2 ring-brand-100 shrink-0">
                                    {{ \Illuminate\Support\Str::of($d->doctorName)->replaceMatches('/^(dr\.?|drg\.?)\s*/i', '')->substr(0, 1)->upper() }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('dokter.show', $d->doctorSlug) }}" class="font-display font-bold text-slate-900 leading-snug line-clamp-2 hover:text-brand-700 transition">{{ $d->doctorName }}</a>
                                <p class="mt-0.5 text-xs text-slate-500 line-clamp-1">{{ $d->doctorSpecialization }}</p>
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-brand-50 text-brand-700 px-2 py-0.5 text-[11px] font-medium">
                                    <iconify-icon icon="ph:hospital-duotone" class="text-sm"></iconify-icon>
                                    {{ $d->polyclinicName }}
                                </span>
                            </div>
                        </div>

                        {{-- Jadwal praktik --}}
                        <div class="border-t border-slate-100 px-5 py-4 flex-1">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Jadwal Praktik</p>
                            <ul class="space-y-1.5">
                                @foreach ($rows->sortBy(fn ($r) => $r->day->dayIndex()) as $r)
                                    <li class="flex items-center justify-between text-sm">
                                        <span class="inline-flex items-center gap-1.5 text-slate-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                                            {{ $r->day->label() }}
                                        </span>
                                        <span class="tabular-nums font-medium text-slate-600">{{ $r->startTime }}–{{ $r->endTime }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <a href="{{ route('dokter.show', $d->doctorSlug) }}"
                           class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-sm font-semibold text-brand-700 hover:bg-brand-50/60 transition">
                            Lihat profil
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl bg-white ring-1 ring-slate-900/5 p-10 text-center text-slate-500 shadow-soft">
                <iconify-icon icon="ph:magnifying-glass-duotone" class="text-4xl text-slate-300"></iconify-icon>
                <p class="mt-2">Tidak ada jadwal sesuai filter.</p>
            </div>
        @endif
    </div>
</div>
