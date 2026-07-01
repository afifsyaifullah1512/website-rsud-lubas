<?php

declare(strict_types=1);

use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Unit test SiteSettingService
|--------------------------------------------------------------------------
|
| Memvalidasi requirement 26.1, 26.2, 26.3:
|  - get/set/all bekerja dengan benar.
|  - all() pada instance yang sama hanya membaca cache application-level
|    paling banyak satu kali (memoisasi per request).
|  - set() melakukan invalidate cache application-level.
|
| Test ini menggunakan SQLite in-memory agar Eloquent model SiteSetting dapat
| beroperasi tanpa membutuhkan MySQL eksternal, dan cache "array" agar tidak
| memerlukan Redis.
*/

uses(Tests\TestCase::class);

beforeEach(function () {
    // Pakai SQLite in-memory dan cache array untuk isolasi penuh.
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

afterEach(function () {
    Schema::connection('sqlite_test')->dropIfExists('site_settings');
});

it('returns default value when key is missing', function () {
    $service = new SiteSettingService();

    expect($service->get('rs_name', 'fallback'))->toBe('fallback');
    expect($service->get('rs_name'))->toBeNull();
});

it('persists value via set() and reads it back via get()', function () {
    $service = new SiteSettingService();

    $service->set('rs_name', 'RSUD Lubuk Basung');

    expect(SiteSetting::query()->where('key', 'rs_name')->exists())->toBeTrue();
    expect($service->get('rs_name'))->toBe('RSUD Lubuk Basung');
});

it('all() returns the full key-value map', function () {
    SiteSetting::query()->create(['key' => 'rs_name', 'value' => 'RSUD Lubas']);
    SiteSetting::query()->create(['key' => 'phone', 'value' => '0752-1234']);

    $service = new SiteSettingService();

    expect($service->all())
        ->toBe([
            'rs_name' => 'RSUD Lubas',
            'phone' => '0752-1234',
        ]);
});

it('memoizes within the same instance so cache is read at most once per request', function () {
    SiteSetting::query()->create(['key' => 'rs_name', 'value' => 'RSUD Lubas']);

    $service = new SiteSettingService();

    // Panggilan pertama mengisi memoisasi instance dan cache application-level.
    $service->all();

    // Hapus cache application-level. Jika service tidak memoisasi, panggilan
    // berikutnya harus mengisi ulang cache dari DB (cache key kembali ada).
    Cache::forget(SiteSettingService::CACHE_KEY);
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeFalse();

    $service->all();

    // Karena instance memoize hasilnya, cache application-level TIDAK boleh
    // diisi ulang oleh panggilan kedua dalam request yang sama.
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeFalse();
});

it('invalidates cache when set() is called', function () {
    $service = new SiteSettingService();

    // Isi cache lebih dulu.
    SiteSetting::query()->create(['key' => 'rs_name', 'value' => 'Lama']);
    $service->all();
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeTrue();

    // Mutasi via service: cache application-level harus di-flush.
    $service->set('rs_name', 'Baru');
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeFalse();

    // get() berikutnya melihat nilai baru.
    expect($service->get('rs_name'))->toBe('Baru');
});

it('rebuilds cache lazily on the next read after invalidation', function () {
    $service = new SiteSettingService();
    $service->set('rs_name', 'RSUD Lubas');

    // set() men-flush cache; pembacaan berikutnya harus mengisi ulang.
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeFalse();

    $service->all();

    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeTrue();
    expect(Cache::get(SiteSettingService::CACHE_KEY))->toBe(['rs_name' => 'RSUD Lubas']);
});

it('observer flushes the application-level cache when SiteSetting is saved', function () {
    SiteSetting::query()->create(['key' => 'rs_name', 'value' => 'Lama']);

    $service = app(SiteSettingService::class);
    $service->all(); // populate cache

    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeTrue();

    // Mutasi melalui instance model agar event `saved` (yang dipantau observer)
    // ter-trigger; ini mensimulasikan Filament resource yang menyimpan record
    // satu per satu.
    $setting = SiteSetting::query()->find('rs_name');
    $setting->value = 'Baru';
    $setting->save();

    // Observer.saved() harus sudah men-flush cache.
    expect(Cache::has(SiteSettingService::CACHE_KEY))->toBeFalse();
});
