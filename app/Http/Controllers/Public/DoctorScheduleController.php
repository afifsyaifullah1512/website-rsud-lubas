<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleIndexRequest;
use App\Models\Doctor;
use App\Services\DoctorScheduleService;
use App\Support\ValueObjects\ScheduleFilter;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Daftar jadwal dokter + halaman detail dokter.
 *
 * Requirement 4.1–4.8.
 */
class DoctorScheduleController extends Controller
{
    public function __construct(
        private readonly DoctorScheduleService $service,
    ) {
    }

    /**
     * Halaman daftar jadwal dokter.
     *
     * Parameter query (polyclinic_id/day/q) divalidasi oleh
     * {@see ScheduleIndexRequest} sehingga input invalid menghasilkan
     * respons 422 (Requirement 4.6, 4.7). Nilai awal diteruskan sebagai
     * props mount komponen Livewire {@see \App\Livewire\DoctorScheduleFilter}
     * yang menangani filter live tanpa reload (Requirement 4.2).
     */
    public function index(ScheduleIndexRequest $request): View
    {
        $filter = ScheduleFilter::fromArray($request->validated());

        return view('public.schedule.index', [
            'pageTitle' => 'Jadwal Dokter',
            'pageDescription' => 'Cari jadwal praktik dokter RSUD berdasarkan poliklinik, hari, atau nama dokter.',
            'polyclinicId' => $filter->polyclinicId,
            'day' => $filter->day?->value,
            'q' => $filter->search,
        ]);
    }

    public function show(string $slug): View
    {
        $doctor = Doctor::query()
            ->with('polyclinic')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first();

        if (! $doctor) {
            throw new NotFoundHttpException();
        }

        return view('public.schedule.doctor', [
            'pageTitle' => $doctor->name,
            'pageDescription' => trim($doctor->name.' — '.$doctor->specialization).'. Lihat profil dan jadwal praktik dokter di RSUD.',
            'doctor' => $doctor,
            'schedules' => $this->service->findByDoctor((int) $doctor->id),
        ]);
    }
}
