@php
    $todayLabel = match ((int) now()->dayOfWeekIso) {
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', default => 'Minggu'
    };
    $settings = app(\App\Services\SiteSettingService::class);
    $schedHeading = $settings->get('home_schedule_heading', \App\Support\SiteContent::text('home_schedule_heading'));
    $schedSub = $settings->get('home_schedule_subheading', \App\Support\SiteContent::text('home_schedule_subheading'));
    $todayList = collect($todaySchedules ?? []);
@endphp
<section class="bg-brand-50 border-t border-brand-100">
    <div class="container-page py-10 md:py-14">
        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <p class="section-eyebrow">Praktik hari ini</p>
                <h2 class="section-heading mt-2">{{ $schedHeading }} {{ $todayLabel }}</h2>
                <p class="text-slate-600 mt-1">{{ $schedSub }}</p>
            </div>
            <a href="{{ route('jadwal') }}" class="hidden md:inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800 shrink-0">
                Semua jadwal
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </a>
        </div>

        @if ($todayList->count() > 0)
            <div class="relative" x-data="{
                dragging: false, startX: 0, scrollLeft: 0,
                dragStart(e) {
                    this.dragging = true; this.startX = e.pageX; this.scrollLeft = e.target.scrollLeft;
                },
                dragMove(e) {
                    if (!this.dragging) return;
                    e.preventDefault();
                    e.target.scrollLeft = this.scrollLeft - (e.pageX - this.startX);
                },
                dragEnd() { this.dragging = false; }
            }">
                <div x-ref="scroller"
                     @mousedown="dragStart($event)"
                     @mousemove="dragMove($event)"
                     @mouseup="dragEnd"
                     @mouseleave="dragEnd"
                     class="flex gap-5 overflow-x-auto pb-4 -mx-4 px-4 snap-x snap-mandatory cursor-grab active:cursor-grabbing"
                     style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
                    @foreach ($todayList as $s)
                        <div class="snap-start shrink-0 w-[280px] sm:w-[320px]">
                            <a href="{{ route('dokter.show', $s->doctorSlug) }}"
                               class="group flex h-full flex-col rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft p-5 transition duration-300 hover:-translate-y-1 hover:shadow-premium hover:ring-brand-200">
                                <div class="flex items-center gap-3">
                                    @if ($s->doctorPhotoUrl)
                                        <img src="{{ $s->doctorPhotoUrl }}" alt="{{ $s->doctorName }}" loading="lazy"
                                             class="h-14 w-14 rounded-xl object-cover bg-brand-50 ring-1 ring-slate-200 shrink-0">
                                    @else
                                        <div class="h-14 w-14 rounded-xl bg-brand-50 text-brand-700 grid place-items-center font-display text-xl font-bold ring-1 ring-brand-100 shrink-0">
                                            {{ \Illuminate\Support\Str::of($s->doctorName)->replaceMatches('/^(dr\.?|drg\.?)\s*/i', '')->substr(0, 1)->upper() }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h3 class="font-display font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-brand-700 transition">{{ $s->doctorName }}</h3>
                                        <p class="mt-0.5 text-xs text-slate-500 line-clamp-1">{{ $s->doctorSpecialization }}</p>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 text-brand-700 px-2.5 py-1 text-[11px] font-medium">
                                            <iconify-icon icon="ph:hospital-duotone" class="text-sm"></iconify-icon>
                                        {{ $s->polyclinicName }}
                                    </span>
                                </div>

                                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3 mt-4">
                                    <span class="inline-flex items-center gap-1 text-sm font-semibold tabular-nums text-slate-700">
                                            <iconify-icon icon="ph:clock-duotone" class="text-base text-brand-600"></iconify-icon>
                                        {{ $s->startTime }}–{{ $s->endTime }}
                                    </span>
                                    <span class="text-xs font-semibold text-brand-700 inline-flex items-center gap-1">
                                        Profil
                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white ring-1 ring-slate-900/5 p-10 text-center text-slate-500 shadow-soft">
                <iconify-icon icon="ph:calendar-x-duotone" class="text-4xl text-slate-300"></iconify-icon>
                <p class="mt-2">Tidak ada jadwal aktif untuk hari {{ $todayLabel }}.</p>
                <a href="{{ route('jadwal') }}" class="mt-4 inline-flex btn-outline-brand btn-sm">Lihat semua jadwal</a>
            </div>
        @endif

        <div class="mt-8 text-center md:hidden">
            <a href="{{ route('jadwal') }}" class="btn-outline-brand btn-sm">Lihat semua jadwal</a>
        </div>
    </div>
</section>

@push('head')
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
@endpush
