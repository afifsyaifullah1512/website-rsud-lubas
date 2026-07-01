<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Paksa lingkungan testing memakai SQLite in-memory + cache/queue array
     * sebelum aplikasi di-bootstrap. Pendekatan ini lebih reliable daripada
     * `<env force="true">` di phpunit.xml karena Laravel sering melakukan
     * caching env di awal proses, sebelum override phpunit ter-apply.
     *
     * Catatan: setiap perubahan di sini WAJIB konsisten dengan `.env.testing`
     * agar developer yang menjalankan test secara langsung (`php artisan test`)
     * tetap mendapat konfigurasi yang sama.
     */
    protected function setUp(): void
    {
        // putenv hanya berlaku jika dipanggil sebelum Laravel boot;
        // parent::setUp() yang memanggil createApplication() membaca env
        // setelah putenv ini di-set, jadi urutannya benar.
        putenv('APP_ENV=testing');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('CACHE_STORE=array');
        putenv('SESSION_DRIVER=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('MAIL_MAILER=array');
        putenv('REDIS_CLIENT=predis');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['CACHE_STORE'] = 'array';
        $_ENV['SESSION_DRIVER'] = 'array';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['MAIL_MAILER'] = 'array';
        $_ENV['REDIS_CLIENT'] = 'predis';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        parent::setUp();

        // Pertahanan kedua: override config secara langsung jika Laravel
        // tetap memuat nilai dari `.env` (mis. karena ada cached config).
        // Konfigurasi ini tidak boleh tergantung pada Redis / MySQL agar
        // test dapat dijalankan di lingkungan minimal (PHP + SQLite).
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('cache.default', 'array');
        config()->set('session.driver', 'array');
        config()->set('queue.default', 'sync');
        config()->set('mail.default', 'array');

        // Spatie Permission membaca cache store SEKALI di constructor
        // PermissionRegistrar (resolusi terjadi saat `Gate::before` di
        // AuthServiceProvider). Override `permission.cache.store` agar
        // PermissionRegistrar memakai array store walau cache.default
        // belum ter-override saat boot pertama.
        config()->set('permission.cache.store', 'array');

        // Ganti definisi cache store `redis` dengan driver `array` agar
        // setiap kode (mis. Spatie\PermissionRegistrar yang sudah cache
        // store di construct-nya) yang TERLANJUR resolve ke 'redis'
        // tetap aman dipanggil tanpa membutuhkan ekstensi `phpredis`.
        config()->set('cache.stores.redis', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Mensterilkan konfigurasi Redis: kosongkan koneksi sehingga
        // `RedisManager` tidak akan men-trigger ekstensi `phpredis`
        // walau ada package yang tidak sengaja menyentuh `Redis::connection()`
        // selama test (tampilan Filament panel, Spatie Permission cache, dll).
        config()->set('database.redis', [
            'client' => 'predis',
            'options' => [],
            'default' => ['url' => null, 'host' => null, 'port' => null, 'database' => 0],
            'cache' => ['url' => null, 'host' => null, 'port' => null, 'database' => 0],
        ]);

        // Paksa cache manager + redis manager + permission registrar
        // re-resolve dengan config baru.
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance('redis');
        $this->app->forgetInstance('redis.connection');
        $this->app->forgetInstance(\Spatie\Permission\PermissionRegistrar::class);
    }
}
