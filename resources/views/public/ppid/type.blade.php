@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $type->label(),
    'description' => 'Dokumen PPID kategori '.$type->label().' sesuai UU KIP No. 14/2008.',
    'breadcrumbs' => [['PPID', route('ppid.index')], [$type->label(), null]],
])

<section class="container-page py-10">
    @if ($isExcluded)
        <div class="card p-4 bg-amber-50 border-amber-200 mb-6">
            <p class="text-sm text-amber-800">
                Informasi yang dikecualikan tidak dapat diunduh langsung. Halaman ini hanya menampilkan
                metadata dan dasar pengecualian sesuai UU KIP No. 14/2008.
            </p>
        </div>
    @endif

    @php $documents = $categories->flatMap->documents; @endphp

    @if ($documents->isEmpty())
        <p class="text-slate-500">Belum ada dokumen pada kategori ini.</p>
    @else
        <div class="grid gap-3">
            @foreach ($documents as $doc)
                <div class="card p-4 flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-semibold text-slate-900">{{ $doc->title }}</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Tahun {{ $doc->year }}
                            @if ($doc->published_at)
                                &middot; Dipublikasikan {{ $doc->published_at->translatedFormat('d M Y') }}
                            @endif
                        </p>
                        @if ($isExcluded)
                            <p class="text-xs text-amber-700 mt-2">
                                Dasar pengecualian: Informasi dikecualikan sesuai UU KIP No. 14/2008.
                            </p>
                        @endif
                    </div>
                    @unless ($isExcluded)
                        <a href="{{ route('ppid.download', $doc->id) }}"
                           class="badge-emerald shrink-0"
                           rel="nofollow">
                            Unduh
                        </a>
                    @else
                        <span class="text-xs text-slate-400 shrink-0">Tidak tersedia untuk diunduh</span>
                    @endunless
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
