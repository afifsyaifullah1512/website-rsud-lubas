<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Berkas media polymorphic (foto/video/file) yang melekat pada
 * entitas seperti Gallery, News, Page, dll.
 *
 * Validates: Requirements 7.2, 20.2.
 *
 * Catatan: Media TIDAK menggunakan LogsActivity untuk menghindari
 * inflasi audit log saat upload massal; perubahan media dapat
 * dilacak melalui parent (mis. Gallery) yang ter-log.
 */
class Media extends Model
{
    use HasFactory;

    /** @var array<int,string> */
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'disk',
        'path',
        'mime',
        'size',
        'caption',
        'sort_order',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
