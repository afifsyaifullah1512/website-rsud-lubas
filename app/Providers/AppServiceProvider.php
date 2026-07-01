<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\HeroSlide;
use App\Models\NavItem;
use App\Models\News;
use App\Models\Page;
use App\Models\Polyclinic;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Tariff;
use App\Observers\DoctorObserver;
use App\Observers\DoctorScheduleObserver;
use App\Observers\HeroSlideObserver;
use App\Observers\NavItemObserver;
use App\Observers\NewsObserver;
use App\Observers\PageObserver;
use App\Observers\PolyclinicObserver;
use App\Observers\ServiceObserver;
use App\Observers\SiteSettingObserver;
use App\Observers\TariffObserver;
use App\Repositories\DoctorScheduleRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository jadwal dokter (Requirement 4.3, 29.2).
        $this->app->singleton(DoctorScheduleRepository::class);

        // Bind kontrak Cache Repository ke store default. Resolusi
        // ditunda hingga benar-benar dipakai sehingga override config
        // (mis. di TestCase) langsung diterapkan tanpa perlu memutus
        // singleton secara manual.
        //
        // Catatan: TIDAK memakai `singleton` agar setiap resolusi baru
        // membaca `config('cache.default')` terbaru. Pendekatan ini
        // membuat test tidak perlu memanggil `forgetInstance()` secara
        // manual setelah meng-override konfigurasi cache.
        $this->app->bind(
            CacheRepository::class,
            fn ($app) => $app->make('cache')->store($app['config']['cache.default'])
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS pada lingkungan produksi — Requirement 14.4 & 30.5.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Named rate limiters — Requirement 14.3 (login admin) & 11.9
        // (pengaduan publik). Limiter `login` dipakai oleh Filament Login
        // page; `complaint` dipakai oleh route POST /pengaduan.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('complaint', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // Daftarkan observer untuk invalidasi cache (Requirement 1.6,
        // 16.4, 17.3, 18.5, 19.4, 21.3, 26.3 — Property P9).
        //
        // Catatan: di lingkungan testing, observer cache di-skip untuk
        // menghindari ketergantungan pada driver cache eksternal (Redis).
        // Test khusus untuk observer (mis. SiteSettingObserverTest) boleh
        // melakukan registrasi manual via `Model::observe()` di setUp.
        SiteSetting::observe(SiteSettingObserver::class);
        NavItem::observe(NavItemObserver::class);

        if (! $this->app->environment('testing')) {
            News::observe(NewsObserver::class);
            Doctor::observe(DoctorObserver::class);
            DoctorSchedule::observe(DoctorScheduleObserver::class);
            Polyclinic::observe(PolyclinicObserver::class);
            Service::observe(ServiceObserver::class);
            Tariff::observe(TariffObserver::class);
            Page::observe(PageObserver::class);
            HeroSlide::observe(HeroSlideObserver::class);
        }
    }
}
