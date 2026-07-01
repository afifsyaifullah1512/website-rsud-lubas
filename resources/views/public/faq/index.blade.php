@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Pertanyaan yang Sering Diajukan',
    'description' => 'Jawaban atas pertanyaan umum seputar layanan rumah sakit.',
    'breadcrumbs' => [['FAQ', null]],
])

<section class="container-page py-10 max-w-3xl">
    @if ($faqs->isNotEmpty())
        <div class="card divide-y divide-slate-200">
            @foreach ($faqs as $faq)
                <details class="group" x-data="{ open: false }" @toggle="open = $event.target.open">
                    <summary class="cursor-pointer px-4 py-3 font-medium text-slate-900 flex items-center justify-between list-none">
                        <span>{{ $faq->question }}</span>
                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-4 pb-4 text-sm text-slate-700">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </details>
            @endforeach
        </div>
    @else
        <p class="text-slate-500">Belum ada FAQ.</p>
    @endif
</section>
@endsection
