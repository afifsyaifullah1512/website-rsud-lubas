@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Pengaduan Terkirim',
    'breadcrumbs' => [['Pengaduan', route('pengaduan.create')], ['Terima Kasih', null]],
])

<section class="container-page py-12 max-w-xl">
    <div class="card p-6">
        <p class="text-sm text-slate-600">Pengaduan Anda telah kami terima dan akan ditindaklanjuti.</p>
        <div class="mt-4 p-4 bg-slate-50 rounded-md border border-slate-200">
            <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Nomor Tiket</p>
            <p class="text-xl font-mono font-bold tracking-wider mt-1 text-slate-900">{{ $complaint->ticket_number }}</p>
            <p class="text-xs text-slate-500 mt-2">Simpan nomor tiket ini untuk melacak status pengaduan.</p>
        </div>
        <div class="mt-5 flex flex-wrap gap-2">
            <a href="{{ route('pengaduan.track', $complaint->ticket_number) }}" class="bg-brand-700 hover:bg-brand-800 text-white px-4 py-2 rounded-md text-sm font-semibold">Lacak Status</a>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-md text-sm text-slate-700 hover:bg-slate-100">Kembali ke Beranda</a>
        </div>
    </div>
</section>
@endsection
