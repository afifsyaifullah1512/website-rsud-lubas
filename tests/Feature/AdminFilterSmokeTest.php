<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    $this->actingAs($this->admin);
});

it('applies News filters', function () {
    Livewire::test(App\Filament\Resources\NewsResource\Pages\ManageNews::class)
        ->filterTable('status', App\Support\Enums\NewsStatus::PUBLISHED->value)
        ->assertSuccessful();
});

it('applies Service type filter', function () {
    Livewire::test(App\Filament\Resources\ServiceResource\Pages\ManageServices::class)
        ->filterTable('type', App\Support\Enums\ServiceType::POLI->value)
        ->assertSuccessful();
});

it('applies Doctor filters', function () {
    Livewire::test(App\Filament\Resources\DoctorResource\Pages\ManageDoctors::class)
        ->filterTable('is_active', true)
        ->assertSuccessful();
});

it('applies DoctorSchedule day filter', function () {
    Livewire::test(App\Filament\Resources\DoctorScheduleResource\Pages\ManageDoctorSchedules::class)
        ->filterTable('day', App\Support\Enums\Day::SENIN->value)
        ->assertSuccessful();
});

it('applies Complaint status filter', function () {
    Livewire::test(App\Filament\Resources\ComplaintResource\Pages\ManageComplaints::class)
        ->filterTable('status', App\Support\Enums\ComplaintStatus::NEW->value)
        ->assertSuccessful();
});

it('applies Gallery type filter', function () {
    Livewire::test(App\Filament\Resources\GalleryResource\Pages\ManageGalleries::class)
        ->filterTable('type', App\Support\Enums\GalleryType::PHOTO->value)
        ->assertSuccessful();
});

it('applies JobVacancy status filter', function () {
    Livewire::test(App\Filament\Resources\JobVacancyResource\Pages\ManageJobVacancies::class)
        ->filterTable('status', App\Support\Enums\JobVacancyStatus::OPEN->value)
        ->assertSuccessful();
});

it('applies Polyclinic active filter', function () {
    Livewire::test(App\Filament\Resources\PolyclinicResource\Pages\ListPolyclinics::class)
        ->filterTable('is_active', true)
        ->assertSuccessful();
});

it('applies HeroSlide active filter', function () {
    Livewire::test(App\Filament\Resources\HeroSlideResource\Pages\ListHeroSlides::class)
        ->filterTable('is_active', true)
        ->assertSuccessful();
});

it('applies NavItem filters', function () {
    Livewire::test(App\Filament\Resources\NavItemResource\Pages\ManageNavItems::class)
        ->filterTable('is_active', true)
        ->assertSuccessful();
});

it('dashboard latest complaints widget sorts/searches', function () {
    Livewire::test(App\Filament\Widgets\LatestComplaints::class)
        ->searchTable('RSUD')
        ->assertSuccessful();
});

it('applies trashed + relationship filters', function () {
    Livewire::test(App\Filament\Resources\NewsResource\Pages\ManageNews::class)
        ->filterTable('trashed', 1)->assertSuccessful()
        ->filterTable('category_id', 1)->assertSuccessful();

    Livewire::test(App\Filament\Resources\ServiceResource\Pages\ManageServices::class)
        ->filterTable('trashed', 0)->assertSuccessful();

    Livewire::test(App\Filament\Resources\DoctorResource\Pages\ManageDoctors::class)
        ->filterTable('trashed', 1)->assertSuccessful()
        ->filterTable('polyclinic_id', 1)->assertSuccessful();

    Livewire::test(App\Filament\Resources\PolyclinicResource\Pages\ListPolyclinics::class)
        ->filterTable('trashed', 1)->assertSuccessful();
});

it('applies NavItem roots_only + Ppid + schedule filters', function () {
    Livewire::test(App\Filament\Resources\NavItemResource\Pages\ManageNavItems::class)
        ->filterTable('roots_only', true)->assertSuccessful();

    Livewire::test(App\Filament\Resources\PpidDocumentResource\Pages\ManagePpidDocuments::class)
        ->filterTable('category_id', 1)->assertSuccessful();

    Livewire::test(App\Filament\Resources\DoctorScheduleResource\Pages\ManageDoctorSchedules::class)
        ->filterTable('polyclinic_id', 1)->assertSuccessful()
        ->filterTable('is_active', true)->assertSuccessful();
});

it('dashboard widget sort by created_at', function () {
    Livewire::test(App\Filament\Widgets\LatestComplaints::class)
        ->sortTable('created_at')
        ->assertSuccessful();
});


