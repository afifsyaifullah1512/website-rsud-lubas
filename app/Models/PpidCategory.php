<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\PpidCategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Kategori PPID (Berkala, Serta-merta, Setiap-saat, Dikecualikan).
 *
 * Validates: Requirements 10.1, 23.1.
 */
class PpidCategory extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'name',
        'type',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'type' => PpidCategoryType::class,
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(PpidDocument::class, 'category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
