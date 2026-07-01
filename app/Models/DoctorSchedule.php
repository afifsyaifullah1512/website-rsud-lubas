<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\Day;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Jadwal praktik dokter pada satu hari kerja.
 *
 * Validates: Requirements 4.3, 18.1, 18.2, 18.4.
 */
class DoctorSchedule extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'doctor_id',
        'polyclinic_id',
        'day',
        'start_time',
        'end_time',
        'note',
        'is_active',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'day' => Day::class,
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    /**
     * Scope: hanya jadwal aktif yang dimiliki dokter aktif.
     *
     * Selaras dengan contoh "Eloquent Model Example" di design.md
     * dan Requirement 4.3.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('doctor', fn (Builder $d) => $d->where('is_active', true));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
