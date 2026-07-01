<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\GalleryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Koleksi galeri foto/video RSUD.
 *
 * Validates: Requirements 7.1, 20.1.
 */
class Gallery extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'description',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'type' => GalleryType::class,
    ];

    /**
     * Media yang dimiliki Gallery (foto / video).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
