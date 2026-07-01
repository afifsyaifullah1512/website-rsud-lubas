<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Dokumen PPID per kategori (UU 14/2008 KIP).
 *
 * Validates: Requirements 10.1, 10.3, 23.1.
 */
class PpidDocument extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'category_id',
        'title',
        'file_path',
        'year',
        'published_at',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'year' => 'integer',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PpidCategory::class, 'category_id');
    }

    /**
     * Scope: hanya dokumen yang sudah dipublikasikan
     * (`published_at` not null AND `<= now()`).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
