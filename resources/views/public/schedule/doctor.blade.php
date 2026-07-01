@extends('layouts.public')

@section('content')
@include('partials._page-header', [
    'title' => $doctor->name,
    'description' => $doctor->specialization,
    'breadcrumbs' => [
        ['Jadwal Dokter', route('jadwal')],
        [$doctor->name, null],
    ],
])

<section class="container-page py-10 grid gap-8 lg:grid-cols-3">
    <aside class="card p-5 lg:col-span-1">
        @if ($doctor->photo)
            <img src="{{ str_starts_with($doctor->photo, 'http') ? $doctor->photo : asset('storage/'.$doctor->photo) }}" alt="{{ $doctor->name }}" class="w-32 h-32 rounded-full object-cover mx-auto bg-slate-100">
        @endif
        <p class="text-center font-semibold text-slate-900 mt-3">{{ $doctor->name }}</p>
        <p class="text-center text-sm text-slate-600">{{ $doctor->specialization }}</p>
        @if ($doctor->polyclinic)
            <div class="mt-4 pt-4 border-t border-slate-200 text-sm">
                <p class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Poliklinik</p>
                <p class="text-slate-700 mt-1">{{ $doctor->polyclinic->name }}</p>
            </div>
        @endif
    </aside>

    <div class="lg:col-span-2 space-y-6">
        @if ($doctor->bio)
            <div class="card p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Tentang</h2>
                <div class="prose-rsud">{!! nl2br(e($doctor->bio)) !!}</div>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Jadwal Praktik</h2>
            </div>
            @if (count($schedules) > 0)
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="text-left px-4 py-2 font-semibold">Hari</th>
                            <th class="text-left px-4 py-2 font-semibold">Jam</th>
                            <th class="text-left px-4 py-2 font-semibold">Poliklinik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($schedules as $s)
                            <tr>
                                <td class="px-4 py-2 text-slate-700">{{ $s->day->label() }}</td>
                                <td class="px-4 py-2 tabular-nums text-slate-700">{{ $s->startTime }} – {{ $s->endTime }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $s->polyclinicName }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-5 py-6 text-slate-500 text-sm">Belum ada jadwal aktif.</p>
            @endif
        </div>
    </div>
</section>
@endsection
