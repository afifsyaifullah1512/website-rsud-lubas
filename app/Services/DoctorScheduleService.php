<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Repositories\DoctorScheduleRepository;
use App\Support\ValueObjects\ScheduleFilter;
use App\Support\ViewModels\DoctorScheduleVM;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

/**
 * Layanan domain untuk jadwal dokter.
 *
 * Implementasi mengikuti Algoritma 1 (design.md):
 *  - Cache.remember dengan TTL 10 menit dan kunci yang konsisten
 *    terhadap parameter filter (Requirement 29.5).
 *  - Hasil berupa `Collection<DoctorScheduleVM>` yang aman dipakai
 *    Blade tanpa membongkar relasi Eloquent (Requirement 4.1).
 *  - Eager loading di repositori menjaga halaman jadwal bebas
 *    N+1 (Requirement 29.2).
 *  - Sortir deterministik (Requirement 4.4 & 4.8).
 *
 * Validates: Requirements 4.1, 4.3, 4.4, 4.8, 29.2, 29.5.
 */
class DoctorScheduleService
{
    /**
     * TTL cache untuk listing jadwal: 10 menit.
     */
    public const CACHE_TTL_SECONDS = 600;

    /**
     * Prefix kunci cache. Dipakai bersama dengan hash filter.
     */
    public const CACHE_KEY_PREFIX = 'schedules:';

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly DoctorScheduleRepository $repository,
    ) {
    }

    /**
     * Ambil daftar jadwal aktif terfilter dalam bentuk view-model.
     *
     * @return Collection<int,DoctorScheduleVM>
     */
    public function listFiltered(ScheduleFilter $filter): Collection
    {
        $cacheKey = self::CACHE_KEY_PREFIX.md5(serialize($filter->toArray()));

        /** @var Collection<int,DoctorScheduleVM> $result */
        $result = $this->cache->remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn (): Collection => $this->repository
                ->queryActive($filter)
                ->get()
                ->map(static fn ($schedule) => DoctorScheduleVM::fromModel($schedule))
                ->values()
        );

        return $result;
    }

    /**
     * Ambil semua jadwal aktif dari satu Doctor untuk halaman
     * `/dokter/{slug}` (Requirement 4.5).
     *
     * @return Collection<int,DoctorScheduleVM>
     */
    public function findByDoctor(int $doctorId): Collection
    {
        $doctor = Doctor::query()
            ->with(['polyclinic'])
            ->where('is_active', true)
            ->find($doctorId);

        if ($doctor === null) {
            return collect();
        }

        return $doctor
            ->schedules()
            ->with('polyclinic')
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp(
                    (string) $a->polyclinic?->name,
                    (string) $b->polyclinic?->name,
                ),
                fn ($a, $b) => $a->day->dayIndex() <=> $b->day->dayIndex(),
                fn ($a, $b) => strcmp((string) $a->start_time, (string) $b->start_time),
            ])
            ->values()
            ->map(static function ($schedule) use ($doctor) {
                // Pastikan relasi doctor.polyclinic tersedia bagi VM tanpa
                // memicu query tambahan (relasi sudah dimuat di atas).
                $schedule->setRelation('doctor', $doctor);

                return DoctorScheduleVM::fromModel($schedule);
            });
    }
}
