<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Support\Enums\Day;
use App\Support\ValueObjects\ScheduleFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query abstraction untuk daftar jadwal dokter.
 *
 * Mengikuti Algoritma 1 (design.md): hanya jadwal aktif milik dokter
 * aktif (non-soft-deleted) yang dimuat, dengan eager loading
 * relasi `doctor.polyclinic` agar bebas N+1 (Requirement 29.2).
 *
 * Sortir deterministik (Requirement 4.4 & 4.8):
 *   1. nama Polyclinic ASC
 *   2. indeks hari ASC (SENIN=1 .. MINGGU=7)
 *   3. start_time ASC
 *
 * Validates: Requirements 4.3, 4.4, 4.8, 29.2.
 */
class DoctorScheduleRepository
{
    /**
     * Bangun Builder yang sudah memfilter jadwal aktif sesuai DTO.
     *
     * Catatan implementasi sortir nama poli:
     *  - Memakai subquery pada `Polyclinic::select('name')` (lihat
     *    `orderBy(Builder)`) agar tidak perlu melakukan JOIN tambahan
     *    pada query utama. Pendekatan ini menjaga kueri sederhana,
     *    bekerja sama dengan eager loading `doctor.polyclinic`, dan
     *    menghasilkan urutan yang deterministik di seluruh driver
     *    yang didukung (MySQL/MariaDB/PostgreSQL/SQLite).
     */
    public function queryActive(ScheduleFilter $filter): Builder
    {
        $query = DoctorSchedule::query()
            ->with(['doctor.polyclinic'])
            ->where('doctor_schedules.is_active', true)
            ->whereHas('doctor', function (Builder $doctor): void {
                $doctor->where('is_active', true)
                    ->whereNull('deleted_at');
            });

        if ($filter->polyclinicId !== null) {
            $query->where('doctor_schedules.polyclinic_id', $filter->polyclinicId);
        }

        if ($filter->day instanceof Day) {
            $query->where('doctor_schedules.day', $filter->day->value);
        }

        if ($filter->search !== null && mb_strlen($filter->search) >= 2) {
            $term = '%'.$filter->search.'%';
            $query->whereHas('doctor', function (Builder $doctor) use ($term): void {
                $doctor->where(function (Builder $w) use ($term): void {
                    $w->where('name', 'LIKE', $term)
                        ->orWhere('specialization', 'LIKE', $term);
                });
            });
        }

        // 1) Urutkan berdasarkan nama Polyclinic via subquery berkorelasi
        $polyclinicNameSubquery = Polyclinic::query()
            ->select('name')
            ->whereColumn('polyclinics.id', 'doctor_schedules.polyclinic_id');

        $query->orderBy($polyclinicNameSubquery);

        // 2) Indeks hari (SENIN=1 .. MINGGU=7) deterministik lintas driver
        $dayOrderRaw = 'CASE doctor_schedules.day'
            ." WHEN 'SENIN' THEN 1"
            ." WHEN 'SELASA' THEN 2"
            ." WHEN 'RABU' THEN 3"
            ." WHEN 'KAMIS' THEN 4"
            ." WHEN 'JUMAT' THEN 5"
            ." WHEN 'SABTU' THEN 6"
            ." WHEN 'MINGGU' THEN 7"
            .' ELSE 8 END';

        $query->orderByRaw($dayOrderRaw);

        // 3) start_time ASC
        $query->orderBy('doctor_schedules.start_time');

        return $query;
    }
}
