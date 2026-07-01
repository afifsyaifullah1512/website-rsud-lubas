<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\News;
use App\Support\CacheKeys;
use App\Support\Enums\NewsStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Publish News terjadwal (Algoritma 4 — design.md, Requirement 6.1–6.4).
 */
class PublishScheduledNews extends Command
{
    protected $signature = 'news:publish-scheduled';

    protected $description = 'Publikasikan News yang dijadwalkan dan sudah jatuh tempo.';

    public function handle(): int
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            /** @var \Illuminate\Support\Collection<int,News> $candidates */
            $candidates = News::query()
                ->where('status', NewsStatus::DRAFT)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($candidates as $news) {
                try {
                    $news->status = NewsStatus::PUBLISHED;
                    $news->save();
                    Cache::forget(CacheKeys::HOME);
                    Cache::forget(CacheKeys::newsSlug((string) $news->slug));
                    $count++;
                } catch (Throwable $e) {
                    Log::error('Gagal publikasi News terjadwal', [
                        'news_id' => $news->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Published {$count} berita.");

        return self::SUCCESS;
    }
}
