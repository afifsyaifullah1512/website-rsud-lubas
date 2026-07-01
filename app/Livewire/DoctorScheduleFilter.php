<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Polyclinic;
use App\Services\DoctorScheduleService;
use App\Support\Enums\Day;
use App\Support\ValueObjects\ScheduleFilter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Komponen interaktif filter jadwal dokter pada halaman publik
 * `/jadwal-dokter`.
 *
 * Memperbarui daftar tanpa memuat ulang seluruh halaman (Requirement 4.2)
 * melalui binding `wire:model.live`. Properti filter disinkronkan ke query
 * string sehingga hasil dapat di-bookmark / dibagikan dan urutan/isi
 * deterministik untuk parameter identik (Requirement 4.8).
 *
 * Seluruh logika domain (filter aktif, sortir, cache) didelegasikan ke
 * {@see DoctorScheduleService::listFiltered()} (Requirement 4.1, 4.3, 4.4).
 *
 * Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.8.
 */
class DoctorScheduleFilter extends Component
{
    #[Url(as: 'polyclinic_id', except: null)]
    public ?int $polyclinicId = null;

    #[Url(as: 'day', except: '')]
    public ?string $day = null;

    #[Url(as: 'q', except: '')]
    public ?string $q = null;

    public function mount(?int $polyclinicId = null, ?string $day = null, ?string $q = null): void
    {
        $this->polyclinicId = $polyclinicId;
        $this->day = $day;
        $this->q = $q;
    }

    /**
     * Kosongkan seluruh filter (tombol "Reset").
     */
    public function resetFilters(): void
    {
        $this->reset(['polyclinicId', 'day', 'q']);
    }

    public function render(): View
    {
        $service = app(DoctorScheduleService::class);

        $filter = ScheduleFilter::fromArray([
            'polyclinicId' => $this->polyclinicId,
            'day' => $this->normalizedDay(),
            'q' => $this->q,
        ]);

        return view('livewire.doctor-schedule-filter', [
            'schedules' => $service->listFiltered($filter),
            'polyclinics' => Polyclinic::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'days' => Day::cases(),
        ]);
    }

    /**
     * Normalisasi nilai `day` menjadi backing-value Day yang valid atau
     * null. Mencegah {@see Day::from()} melempar exception bila menerima
     * masukan tak dikenal (Requirement 4.7 — input invalid diabaikan dan
     * daftar tetap dirender).
     */
    private function normalizedDay(): ?string
    {
        if ($this->day === null || $this->day === '') {
            return null;
        }

        return Day::tryFrom($this->day)?->value;
    }
}
