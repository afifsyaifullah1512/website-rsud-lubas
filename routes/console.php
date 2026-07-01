<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler RSUD
|--------------------------------------------------------------------------
| Memenuhi Requirements 6.1 (publish news), 9.4 (auto-close vacancy),
| 27.1 (sitemap), 31.1 (backup).
*/

ScheduleFacade::command('news:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

ScheduleFacade::command('vacancy:auto-close')->dailyAt('00:05');

ScheduleFacade::command('sitemap:generate')->dailyAt('01:00');

// Backup harian (memerlukan paket spatie/laravel-backup yang sudah dipasang).
ScheduleFacade::command('backup:clean')->dailyAt('01:30');
ScheduleFacade::command('backup:run')->dailyAt('02:00');
