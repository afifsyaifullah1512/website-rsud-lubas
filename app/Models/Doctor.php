<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Dokter yang bertugas pada satu Polyclinic.
 *
 * Validates: Requirements 4.3, 4.5, 18.1, 33.1.
 */
class Doctor extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /** @var array<int,string> */
    protected $fillable = [
        'polyclinic_id',
        'name',
        'slug',
        'photo',
        'specialization',
        'bio',
        'is_active',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /**
     * Scope: hanya Doctor aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
