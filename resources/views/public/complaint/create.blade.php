@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Pengaduan Masyarakat',
    'description' => 'Sampaikan keluhan atau saran. Anda akan menerima nomor tiket untuk melacak status tindak lanjut.',
    'breadcrumbs' => [['Pengaduan', null]],
])

<section class="container-page py-10 grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2">
        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-md mb-6 text-sm">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @livewire('complaint-form')

        <p class="text-xs text-slate-500 mt-3">Maksimal 3 pengaduan per IP per jam. Form dilengkapi reCAPTCHA untuk mencegah spam.</p>
    </div>

    <aside class="space-y-4" id="track">
        <div class="card p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">Lacak Pengaduan</h2>
            <form method="get" onsubmit="event.preventDefault(); window.location='/pengaduan/cek/' + encodeURIComponent(this.ticket.value);" class="flex gap-2">
                <input name="ticket" type="text" required placeholder="RSUD-YYYYMMDD-XXXXXX" class="flex-1 border-slate-300 rounded-md text-sm">
                <button class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-md text-sm">Lacak</button>
            </form>
        </div>

        <div class="card p-5 text-sm text-slate-700 space-y-2">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Catatan</h2>
            <p>Identitas pengadu hanya digunakan untuk keperluan tindak lanjut dan tidak dipublikasikan.</p>
            <p>Untuk kondisi gawat darurat, silakan hubungi langsung IGD.</p>
        </div>
    </aside>
</section>
@endsection
