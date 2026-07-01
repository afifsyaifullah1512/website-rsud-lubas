<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\NewsStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Artikel berita / pengumuman RSUD.
 *
 * Validates: Requirements 5.2, 5.3, 19.1, 19.5, 33.1.
 */
class News extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /** @var string */
    protected $table = 'news';

    /** @var array<int,string> */
    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'status',
        'published_at',
        'views',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'status' => NewsStatus::class,
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Media yang melekat pada berita (lampiran/galeri inline).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Scope: hanya berita yang sudah dipublikasikan dan
     * `published_at <= now()` (Requirement 5.2, P4).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', NewsStatus::PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
