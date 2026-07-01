<?php

declare(strict_types=1);

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Services\DoctorScheduleService;
use App\Support\Enums\Day;
use App\Support\ValueObjects\ScheduleFilter;
use App\Support\ViewModels\DoctorScheduleVM;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

/**
 * Validates: Requirements 4.1, 4.3, 4.4, 4.8, 29.5.
 */

it('returns Collection of DoctorScheduleVM for active schedules ordered deterministically', function () {
    $poliBedah = Polyclinic::factory()->create(['name' => 'Poliklinik Bedah']);
    $poliAnak  = Polyclinic::factory()->create(['name' => 'Poliklinik Anak']);

    $drBedah = Doctor::factory()->create([
        'polyclinic_id' => $poliBedah->id,
        'name'          => 'dr. Bagas',
        'is_active'     => true,
    ]);
    $drAnak = Doctor::factory()->create([
        'polyclinic_id' => $poliAnak->id,
        'name'          => 'dr. Anita',
        'is_active'     => true,
    ]);

    // Dibuat dengan urutan teracak — service harus mengurutkan ulang.
    DoctorSchedule::factory()->create([
        'doctor_id'     => $drBedah->id,
        'polyclinic_id' => $poliBedah->id,
        'day'           => Day::SELASA->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true,
    ]);
    DoctorSchedule::factory()->create([
        'doctor_id'     => $drAnak->id,
        'polyclinic_id' => $poliAnak->id,
        'day'           => Day::SENIN->value,
        'start_time'    => '13:00',
        'end_time'      => '15:00',
        'is_active'     => true,
    ]);
    DoctorSchedule::factory()->create([
        'doctor_id'     => $drAnak->id,
        'polyclinic_id' => $poliAnak->id,
        'day'           => Day::SENIN->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true,
    ]);
    DoctorSchedule::factory()->create([
        'doctor_id'     => $drAnak->id,
        'polyclinic_id' => $poliAnak->id,
        'day'           => Day::RABU->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true,
    ]);

    /** @var DoctorScheduleService $service */
    $service = app(DoctorScheduleService::class);
    $result = $service->listFiltered(new ScheduleFilter());

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(4);
    $result->each(fn ($vm) => expect($vm)->toBeInstanceOf(DoctorScheduleVM::class));

    // Urutan deterministik: nama poli ASC → day_index ASC → start_time ASC.
    $tuples = $result
        ->map(fn (DoctorScheduleVM $vm) => [
            $vm->polyclinicName,
            $vm->day->dayIndex(),
            $vm->startTime,
        ])
        ->values()
        ->all();

    expect($tuples)->toBe([
        ['Poliklinik Anak',  1, '08:00'],
        ['Poliklinik Anak',  1, '13:00'],
        ['Poliklinik Anak',  3, '08:00'],
        ['Poliklinik Bedah', 2, '08:00'],
    ]);
});

it('excludes inactive schedules and schedules of inactive or soft-deleted doctors', function () {
    $poli = Polyclinic::factory()->create(['name' => 'Poliklinik Umum']);

    $aktif = Doctor::factory()->create(['polyclinic_id' => $poli->id, 'is_active' => true]);
    $tidakAktif = Doctor::factory()->create(['polyclinic_id' => $poli->id, 'is_active' => false]);
    $dihapus = Doctor::factory()->create(['polyclinic_id' => $poli->id, 'is_active' => true]);

    $jadwalAktif = DoctorSchedule::factory()->create([
        'doctor_id'     => $aktif->id,
        'polyclinic_id' => $poli->id,
        'day'           => Day::SENIN->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true,
    ]);

    DoctorSchedule::factory()->create([
        'doctor_id'     => $aktif->id,
        'polyclinic_id' => $poli->id,
        'day'           => Day::SELASA->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => false, // jadwal nonaktif harus dikecualikan
    ]);

    DoctorSchedule::factory()->create([
        'doctor_id'     => $tidakAktif->id,
        'polyclinic_id' => $poli->id,
        'day'           => Day::RABU->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true, // dokter nonaktif → harus dikecualikan
    ]);

    DoctorSchedule::factory()->create([
        'doctor_id'     => $dihapus->id,
        'polyclinic_id' => $poli->id,
        'day'           => Day::KAMIS->value,
        'start_time'    => '08:00',
        'end_time'      => '10:00',
        'is_active'     => true,
    ]);
    $dihapus->delete(); // soft-delete → jadwal harus dikecualikan

    /** @var DoctorScheduleService $service */
    $service = app(DoctorScheduleService::class);
    $result = $service->listFiltered(new ScheduleFilter());

    expect($result)->toHaveCount(1);
    expect($result->first()->id)->toBe($jadwalAktif->id);
});
