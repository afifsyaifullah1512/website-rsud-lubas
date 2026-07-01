<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Pengaturan situs (key-value JSON) — `site_settings`.
 *
 * Primary key adalah string `key`; lihat requirements 12.1, 26.1, 26.2.
 */
class SiteSetting extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $primaryKey = 'key';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<int,string> */
    protected $fillable = [
        'key',
        'value',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'value' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
