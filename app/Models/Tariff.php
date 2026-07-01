<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\TariffClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Item tarif yang melekat pada Service.
 *
 * Validates: Requirements 8.4, 21.1, 21.2.
 */
class Tariff extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'service_id',
        'item_name',
        'price',
        'class',
        'note',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'price' => 'decimal:2',
        'class' => TariffClass::class,
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
