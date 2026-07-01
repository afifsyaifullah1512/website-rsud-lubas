<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Riwayat transisi status Complaint (timeline tindak lanjut).
 *
 * Validates: Requirements 11.3, 24.2, 24.3.
 */
class ComplaintLog extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'complaint_id',
        'user_id',
        'status',
        'note',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'status' => ComplaintStatus::class,
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
