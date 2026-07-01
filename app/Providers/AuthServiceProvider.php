<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Gallery;
use App\Models\HeroSlide;
use App\Models\JobVacancy;
use App\Models\NavItem;
use App\Models\News;
use App\Models\Page;
use App\Models\Polyclinic;
use App\Models\PpidDocument;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Tariff;
use App\Models\User;
use App\Policies\ComplaintPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\DoctorSchedulePolicy;
use App\Policies\GalleryPolicy;
use App\Policies\HeroSlidePolicy;
use App\Policies\JobVacancyPolicy;
use App\Policies\NavItemPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PagePolicy;
use App\Policies\PolyclinicPolicy;
use App\Policies\PpidDocumentPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SiteSettingPolicy;
use App\Policies\TariffPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Auth service provider.
 *
 * Mendaftarkan policy untuk setiap model resource sehingga
 * Filament/Controller dapat memanggil `$user->can('news.publish')`
 * atau Gate::authorize('publish', $news) dengan validasi RBAC
 * berbasis Spatie Permission (Requirements 15.2–15.5, 19.3,
 * 24.4, 25.3).
 *
 * Catatan implementasi: Laravel 11 mendukung auto-discovery
 * policy via konvensi `App\Policies\{Model}Policy`. Registrasi
 * eksplisit di sini menambahkan kepastian pemetaan dan menjadi
 * dokumentasi tunggal yang mudah dibaca tim.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan model → policy untuk seluruh resource RBAC.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        News::class            => NewsPolicy::class,
        Doctor::class          => DoctorPolicy::class,
        DoctorSchedule::class  => DoctorSchedulePolicy::class,
        Polyclinic::class      => PolyclinicPolicy::class,
        Service::class         => ServicePolicy::class,
        Tariff::class          => TariffPolicy::class,
        Gallery::class         => GalleryPolicy::class,
        HeroSlide::class       => HeroSlidePolicy::class,
        JobVacancy::class      => JobVacancyPolicy::class,
        PpidDocument::class    => PpidDocumentPolicy::class,
        Complaint::class       => ComplaintPolicy::class,
        Page::class            => PagePolicy::class,
        User::class            => UserPolicy::class,
        SiteSetting::class     => SiteSettingPolicy::class,
        NavItem::class         => NavItemPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate global untuk super-admin: auto-allow seluruh aksi.
        // Ini melengkapi syncPermissions() di RoleSeeder dan menjadi
        // safety net jika ada permission baru ditambahkan tanpa
        // melalui migrasi seeder. Lihat task 11.3 untuk konfirmasi
        // sidebar Filament.
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });
    }
}
