<?php

declare(strict_types=1);

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Services\ScheduleService;
use App\Support\Enums\Day;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| ScheduleService::checkOverlap (Algoritma 2 — design.md)
|--------------------------------------------------------------------------
|
| Test ini memvalidasi perilaku deteksi bentrok pada interval setengah
| terbuka [start, end). Bentrok bila start < s.end_time AND end > s.start_time.
|
| Validates: Requirements 18.2, 18.3, 18.4.
|
*/

uses(RefreshDatabase::class);

/**
 * Helper: buat satu Polyclinic + Doctor + DoctorSchedule baseline.
 *
 * @return array{polyclinic:Polyclinic,doctor:Doctor,schedule:DoctorSchedule}
 */
function seedBaselineSchedule(
    string $start = '08:00',
    string $end = '12:00',
    Day $day = Day::SENIN,
    bool $isActive = true,
): array {
    $polyclinic = Polyclinic::create([
        'name' => 'Poli Penyakit Dalam',
        'slug' => 'poli-penyakit-dalam-'.uniqid(),
        'description' => null,
        'icon' => null,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $doctor = Doctor::create([
        'polyclinic_id' => $polyclinic->id,
        'name' => 'dr. Andi',
        'slug' => 'dr-andi-'.uniqid(),
        'photo' => null,
        'specialization' => 'Penyakit Dalam',
        'bio' => null,
        'is_active' => true,
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->id,
        'polyclinic_id' => $polyclinic->id,
        'day' => $day,
        'start_time' => $start,
        'end_time' => $end,
        'note' => null,
        'is_active' => $isActive,
    ]);

    return ['polyclinic' => $polyclinic, 'doctor' => $doctor, 'schedule' => $schedule];
}

it('detects overlap when new interval is fully inside existing schedule', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($doctor->id, Day::SENIN, '08:00', '09:00'))->toBeTrue();
});

it('does not detect overlap on touching boundary at end (half-open interval)', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    // Existing 08:00-12:00; new 12:00-13:00 hanya bersinggungan, tidak bentrok.
    expect($service->checkOverlap($doctor->id, Day::SENIN, '12:00', '13:00'))->toBeFalse();
});

it('does not detect overlap when new interval is fully after existing', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($doctor->id, Day::SENIN, '13:00', '14:00'))->toBeFalse();
});

it('detects overlap when new interval straddles existing start boundary', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    // Existing 08:00-12:00; new 07:00-09:00 overlap pada 08:00-09:00.
    expect($service->checkOverlap($doctor->id, Day::SENIN, '07:00', '09:00'))->toBeTrue();
});

it('returns false when the only overlapping schedule is excluded by id', function () {
    ['doctor' => $doctor, 'schedule' => $schedule] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($doctor->id, Day::SENIN, '08:00', '09:00', $schedule->id))
        ->toBeFalse();
});

it('does not detect overlap for a different doctor on the same day and time', function () {
    ['polyclinic' => $polyclinic] = seedBaselineSchedule();

    // Dokter kedua di poli yang sama, jadwal sama persis tetapi
    // pencarian dilakukan untuk dokter lain.
    $otherDoctor = Doctor::create([
        'polyclinic_id' => $polyclinic->id,
        'name' => 'dr. Budi',
        'slug' => 'dr-budi-'.uniqid(),
        'photo' => null,
        'specialization' => 'Penyakit Dalam',
        'bio' => null,
        'is_active' => true,
    ]);

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($otherDoctor->id, Day::SENIN, '08:00', '09:00'))->toBeFalse();
});

it('does not detect overlap on a different day for the same doctor', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($doctor->id, Day::SELASA, '08:00', '09:00'))->toBeFalse();
});

it('ignores inactive schedules when checking overlap', function () {
    ['doctor' => $doctor] = seedBaselineSchedule(isActive: false);

    $service = app(ScheduleService::class);

    expect($service->checkOverlap($doctor->id, Day::SENIN, '08:00', '09:00'))->toBeFalse();
});

it('throws InvalidArgumentException when start equals end', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect(fn () => $service->checkOverlap($doctor->id, Day::SENIN, '10:00', '10:00'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws InvalidArgumentException when start is greater than end', function () {
    ['doctor' => $doctor] = seedBaselineSchedule();

    $service = app(ScheduleService::class);

    expect(fn () => $service->checkOverlap($doctor->id, Day::SENIN, '15:00', '14:00'))
        ->toThrow(InvalidArgumentException::class);
});
