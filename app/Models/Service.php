<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Layanan rumah sakit (Poli, Rawat Inap, IGD, Penunjang, Unggulan).
 *
 * Validates: Requirements 3.1, 3.4, 17.2, 33.1.
 */
class Service extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /** @var array<int,string> */
    protected $fillable = [
        'polyclinic_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'type',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'type' => ServiceType::class,
    ];

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
