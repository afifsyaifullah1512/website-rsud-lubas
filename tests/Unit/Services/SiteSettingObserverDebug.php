<?php

declare(strict_types=1);

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'sqlite_test');
    config()->set('database.connections.sqlite_test', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);
    config()->set('cache.default', 'array');

    DB::purge('sqlite_test');

    Schema::connection('sqlite_test')->create('site_settings', function ($table) {
        $table->string('key', 100)->primary();
        $table->json('value');
        $table->timestamps();
    });

    Cache::store('array')->flush();
});

it('debug observer registration', function () {
    expect(Event::hasListeners('eloquent.saved: '.SiteSetting::class))
        ->toBeTrue('Observer should be registered for saved event');

    expect(Event::hasListeners('eloquent.deleted: '.SiteSetting::class))
        ->toBeTrue('Observer should be registered for deleted event');
});
