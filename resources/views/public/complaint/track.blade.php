@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Status Pengaduan',
    'description' => 'Tiket: '.$complaint->ticket_number,
    'breadcrumbs' => [['Pengaduan', route('pengaduan.create')], ['Lacak', null]],
])

<section class="container-page py-10 max-w-2xl space-y-5">
    @php($status = $complaint->status instanceof \App\Support\Enums\ComplaintStatus ? $complaint->status : \App\Support\Enums\ComplaintStatus::from((string) $complaint->status))
    <div class="card p-5">
        <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Subjek</p>
        <p class="text-slate-900 mt-1">{{ $complaint->subject }}</p>
        <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold mt-4">Status Saat Ini</p>
        <p class="font-semibold text-slate-900 mt-1">{{ $status->label() }}</p>
    </div>

    <div class="card p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-3">Riwayat</h2>
        <ol class="relative border-l border-slate-200 ml-3 space-y-4">
            @foreach ($complaint->logs as $log)
                @php($logStatus = $log->status instanceof \App\Support\Enums\ComplaintStatus ? $log->status : \App\Support\Enums\ComplaintStatus::from((string) $log->status))
                <li class="ml-4">
                    <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-brand-600"></div>
                    <p class="text-sm font-medium text-slate-900">{{ $logStatus->label() }}</p>
                    <p class="text-xs text-slate-500">{{ $log->created_at->translatedFormat('d M Y H:i') }}</p>
                    @if ($log->note)
                        <p class="text-sm text-slate-700 mt-1">{{ $log->note }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endsection
