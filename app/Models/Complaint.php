<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Pengaduan publik dari Public_Site.
 *
 * Validates: Requirements 11.3, 11.5, 11.9, 24.5, 32.2.
 *
 * PII: kolom `message`, `email`, `phone`, dan `ip_address` TIDAK
 * boleh masuk ke audit log (Requirement 24.5, 32.2).
 */
class Complaint extends Model
{
    use HasFactory;
    use LogsActivity;

    /** @var array<int,string> */
    protected $fillable = [
        'ticket_number',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'status' => ComplaintStatus::class,
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['message', 'email', 'phone', 'ip_address']);
    }
}
