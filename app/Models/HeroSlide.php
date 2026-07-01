<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Slide pada hero slider (carousel) beranda yang dikelola admin.
 *
 * Tiap slide membawa gambar wajib (`image_path`) serta field teks/CTA
 * opsional. Hanya slide `is_active = true` yang dirender di Public_Site,
 * terurut menaik berdasarkan `sort_order`.
 *
 * Validates: Requirements 35.2, 36.2, 36.7.
 */
class HeroSlide extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var string */
    protected $table = 'hero_slides';

    /** @var array<int,string> */
    protected $fillable = [
        'image_path',
        'headline',
        'subheadline',
        'cta_label',
        'cta_url',
        'sort_order',
        'is_active',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: hanya slide aktif, terurut menaik berdasarkan `sort_order`
     * (untuk render carousel publik).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
