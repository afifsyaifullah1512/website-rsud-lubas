<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Konfigurasi panel Admin (Filament) di URL `/admin`.
 *
 * Memenuhi:
 * - Requirement 14.1: pengguna anonim yang mengakses `/admin`
 *   diredireksi ke halaman login oleh middleware `Authenticate`.
 * - Requirement 14.2: login berhasil membuat sesi & meredireksi ke
 *   dashboard panel.
 * - Requirement 14.3: rate-limit 5 percobaan login / menit / IP
 *   diberlakukan oleh halaman login Filament secara default
 *   (lihat `Filament\Pages\Auth\Login::authenticate()`), selaras
 *   dengan named limiter `login` yang didaftarkan di
 *   {@see \App\Providers\AppServiceProvider}.
 * - Requirement 14.5: timeout sesi mengikuti `config/session.php`
 *   (`SESSION_LIFETIME`, default 120 menit) – middleware
 *   `AuthenticateSession` memaksa logout jika sesi tidak valid.
 * - Requirement 15.1–15.5: otorisasi via `HasRoles` (Spatie) +
 *   {@see \App\Models\User::canAccessPanel()} + Policies (didaftarkan
 *   per resource pada tugas terkait).
 *
 * Otorisasi resource menggunakan `spatie/laravel-permission` melalui
 * Policies + Gate; tidak ada plugin Filament tambahan yang dipasang
 * (paket `filament/spatie-laravel-permission-plugin` tidak ada di
 * Packagist; integrasi native Spatie ↔ Policy sudah memadai).
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Resolve nama RS + logo dari SiteSetting (cached). Aman bahkan
        // sebelum boot DB lengkap karena SiteSettingService memberikan
        // fallback ke default.
        $brandName = 'RSUD Lubas';
        $brandLogo = null;
        $themeColor = 'emerald';
        try {
            /** @var \App\Services\SiteSettingService $settings */
            $settings = app(\App\Services\SiteSettingService::class);
            $brandName = (string) $settings->get('rs_name', $brandName);
            $themeColor = (string) $settings->get('theme_color', 'emerald');
            $logo = $settings->get('logo');
            if ($logo) {
                $brandLogo = str_starts_with($logo, 'http') ? $logo : '/storage/'.ltrim($logo, '/');
            }
        } catch (\Throwable) {
            // Pengaturan belum tersedia (mis. saat artisan run sebelum migrate).
        }

        $colorMap = [
            'emerald' => Color::Emerald,
            'blue' => Color::Blue,
            'sky' => Color::Sky,
            'teal' => Color::Teal,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'rose' => Color::Rose,
            'amber' => Color::Amber,
            'orange' => Color::Orange,
            'red' => Color::Red,
            'slate' => Color::Slate,
        ];
        $primaryColor = $colorMap[$themeColor] ?? Color::Emerald;

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->brandName($brandName)
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile()
            ->colors([
                'primary' => $primaryColor,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Layanan',
                'Dokter & Jadwal',
                'Konten',
                'PPID',
                'Pengaduan',
                'Karir',
                'Pengguna & Peran',
                'Pengaturan',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\LatestComplaints::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if ($brandLogo) {
            // Sidebar: logo + nama horizontal
            $sidebarHtml = '<div class="flex items-center gap-2"><img src="' . e($brandLogo) . '" alt="' . e($brandName) . '" class="h-8"><span class="font-bold text-sm tracking-tight">' . e($brandName) . '</span></div>';
            $panel = $panel
                ->brandLogo(new \Illuminate\Support\HtmlString($sidebarHtml))
                ->brandLogoHeight('2.5rem');
        }

        // Login page: override logo jadi vertikal (logo atas, nama bawah)
        $loginLogoHtml = '<div class="flex flex-col items-center gap-2"><img src="' . e($brandLogo ?? '') . '" alt="' . e($brandName) . '" class="h-16"><span class="text-lg font-bold text-gray-800 dark:text-gray-100">' . e($brandName) . '</span></div>';
        $panel = $panel->renderHook(
            'panels::auth.login.form.before',
            fn () => '<style>.fi-simple-header{display:none!important}.fi-simple-main > .fi-logo{display:none!important}</style>' . $loginLogoHtml,
        );

        // Tampilkan nama RS sebagai heading di dashboard
        $panel = $panel->renderHook(
            'panels::page.header-widgets.before',
            fn () => view('filament.dashboard-header', ['name' => $brandName]),
            scopes: Pages\Dashboard::class,
        );

        return $panel;
    }
}
