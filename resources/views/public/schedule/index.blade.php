@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => 'Jadwal Dokter',
    'description' => 'Cari dokter berdasarkan poliklinik, hari, atau nama. Pastikan datang sesuai jam praktik.',
    'breadcrumbs' => [['Jadwal Dokter', null]],
])

<section class="container-page py-10">
    @livewire('doctor-schedule-filter', [
        'polyclinicId' => $polyclinicId,
        'day' => $day,
        'q' => $q,
    ])
</section>
@endsection
