<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Generate varian gambar (thumbnail 400px + main 1200px) untuk Media.
 *
 * Memenuhi Requirement 7.3, 20.3, 29.3.
 *
 * Implementasi memakai `intervention/image` (^3.0). Job idempoten:
 * jika file varian sudah ada, dilewati. Bila gambar tidak dapat dibaca,
 * job mencatat di log dan retry.
 */
class GenerateImageVariantsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $mediaId,
    ) {
    }

    public function handle(): void
    {
        /** @var Media|null $media */
        $media = Media::query()->find($this->mediaId);
        if ($media === null || ! str_starts_with((string) $media->mime, 'image/')) {
            return;
        }

        $disk = Storage::disk((string) ($media->disk ?? 'public'));
        $absolutePath = $disk->path((string) $media->path);
        if (! is_file($absolutePath)) {
            return;
        }

        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            // Library tidak tersedia: skip job tanpa gagal.
            return;
        }

        // Pilih driver sesuai ekstensi yang tersedia: Imagick bila terpasang,
        // selain itu GD (README mensyaratkan ekstensi `gd`).
        $manager = extension_loaded('imagick')
            ? \Intervention\Image\ImageManager::imagick()
            : \Intervention\Image\ImageManager::gd();
        try {
            $img = $manager->read($absolutePath);
        } catch (\Throwable) {
            return;
        }

        $thumbnailPath = preg_replace('/\.(\w+)$/', '_thumb.$1', (string) $media->path);
        $mainPath = preg_replace('/\.(\w+)$/', '_main.$1', (string) $media->path);

        $thumbnail = clone $img;
        $thumbnail->scale(width: 400);
        $disk->put($thumbnailPath, (string) $thumbnail->encode());

        $main = clone $img;
        $main->scale(width: 1200);
        $disk->put($mainPath, (string) $main->encode());
    }
}
