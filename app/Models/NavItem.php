<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Item menu navigasi publik yang dapat dikelola dari Admin_Panel.
 *
 * 1 level dropdown: `parent_id = NULL` adalah root, sisanya child.
 *
 * Url bebas:
 *  - relatif (`/jadwal-dokter`)
 *  - eksternal (`https://...`) — set `opens_new_tab = true`
 *  - slug page custom (`/halaman/visi-misi`)
 */
class NavItem extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var string */
    protected $table = 'nav_items';

    /** @var array<int,string> */
    protected $fillable = [
        'parent_id',
        'label',
        'url',
        'opens_new_tab',
        'is_active',
        'sort_order',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'opens_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('label');
    }

    /** Item-item aktif. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Hanya item root (tidak punya parent). */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
