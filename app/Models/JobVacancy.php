<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\JobVacancyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Pengumuman lowongan kerja RSUD.
 *
 * Validates: Requirements 9.1, 22.1, 22.2.
 */
class JobVacancy extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'open_at',
        'close_at',
        'attachment',
        'status',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'open_at' => 'date',
        'close_at' => 'date',
        'status' => JobVacancyStatus::class,
    ];

    /**
     * Scope: lowongan terbuka dengan `close_at >= today` (Requirement 9.1, 22.3).
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', JobVacancyStatus::OPEN)
            ->where('close_at', '>=', Carbon::today());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
