<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Halaman statis berbasis slug (sejarah, visi-misi, struktur, sambutan).
 *
 * Validates: Requirements 2.2, 16.2.
 */
class Page extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'slug',
        'title',
        'body',
    ];

    /**
     * Media polymorphic yang melekat pada Page (lihat ERD design.md).
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
