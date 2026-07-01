@extends('layouts.public')

@php use App\Support\Enums\PpidCategoryType; @endphp

@section('content')
@include('partials._page-header', [
    'title' => 'PPID',
    'description' => 'Pejabat Pengelola Informasi & Dokumentasi — transparansi informasi publik RSUD sesuai UU KIP No. 14/2008.',
    'breadcrumbs' => [['PPID', null]],
])

<section class="container-page py-10 space-y-10">
    @foreach ($groups as $group)
        @php
            /** @var PpidCategoryType $type */
            $type = $group['type'];
            $isExcluded = $type === PpidCategoryType::DIKECUALIKAN;
            $documents = $group['categories']->flatMap->documents;
        @endphp

        <div>
            <div class="flex flex-wrap items-end justify-between gap-2 border-b border-slate-200 pb-2 mb-4">
                <h2 class="font-display text-xl font-bold text-slate-900">{{ $type->label() }}</h2>
                <a href="{{ route('ppid.type', $type->slug()) }}" class="text-sm text-brand-700 hover:underline">
                    Lihat semua &rarr;
                </a>
            </div>

            @if ($isExcluded)
                <p class="text-sm text-slate-600 mb-4">
                    Informasi yang dikecualikan tidak dapat diunduh langsung. Hanya metadata dan dasar
                    pengecualian yang ditampilkan sesuai UU KIP No. 14/2008.
                </p>
            @endif

            @if ($documents->isEmpty())
                <p class="text-slate-500 text-sm">Belum ada dokumen pada kategori ini.</p>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($documents as $doc)
                        <div class="group flex items-start gap-3 rounded-2xl bg-white ring-1 ring-slate-900/5 shadow-soft p-4 transition hover:-translate-y-0.5 hover:shadow-premium">
                            <div class="h-11 w-11 rounded-xl {{ $isExcluded ? 'bg-amber-50 text-amber-600' : 'bg-brand-50 text-brand-600' }} grid place-items-center shrink-0">
                                <iconify-icon icon="{{ $isExcluded ? 'ph:lock-key-duotone' : 'ph:file-text-duotone' }}" class="text-2xl"></iconify-icon>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-slate-900 leading-snug">{{ $doc->title }}</h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Tahun {{ $doc->year }}
                                    @if ($doc->published_at)
                                        &middot; {{ $doc->published_at->translatedFormat('d M Y') }}
                                    @endif
                                </p>
                                @if ($isExcluded)
                                    <p class="text-xs text-amber-700 mt-2">Dikecualikan sesuai UU KIP No. 14/2008.</p>
                                    <span class="mt-2 inline-flex items-center gap-1 text-xs text-slate-400">
                                        <iconify-icon icon="ph:download-simple-duotone"></iconify-icon> Tidak tersedia untuk diunduh
                                    </span>
                                @else
                                    <a href="{{ route('ppid.download', $doc->id) }}" rel="nofollow"
                                       class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition">
                                        <iconify-icon icon="ph:download-simple-duotone" class="text-base"></iconify-icon> Unduh dokumen
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</section>
@endsection
