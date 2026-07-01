@extends('layouts.public')

@section('content')
<section class="container-page py-20 max-w-lg">
    <p class="text-sm font-semibold text-amber-700">429</p>
    <h1 class="mt-2 text-2xl md:text-3xl font-bold text-slate-900">Terlalu Banyak Permintaan</h1>
    <p class="text-slate-600 mt-2">Anda telah melampaui batas permintaan yang diizinkan. Silakan tunggu beberapa saat sebelum mencoba kembali.</p>
    <a href="{{ url()->previous() }}" class="mt-5 inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white px-4 py-2 rounded-md text-sm font-semibold">Kembali</a>
</section>
@endsection
