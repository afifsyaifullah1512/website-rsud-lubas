<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Gallery;
use App\Models\HeroSlide;
use App\Models\Polyclinic;
use App\Models\Service;
use App\Services\DoctorScheduleService;
use App\Services\NewsService;
use App\Services\SiteSettingService;
use App\Support\CacheKeys;
use App\Support\Enums\Day;
use App\Support\Enums\ServiceType;
use App\Support\ValueObjects\ScheduleFilter;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\View\View;

/**
 * Beranda publik — Requirement 1.1–1.6.
 */
class HomeController extends Controller
{
    public function __construct(
        private readonly NewsService $news,
        private readonly DoctorScheduleService $schedules,
        private readonly CacheRepository $cache,
    ) {
    }

    public function index(): View
    {
        // Cache 5 menit untuk komposit halaman beranda (Requirement 1.5).
        $payload = $this->cache->remember(
            CacheKeys::HOME,
            now()->addMinutes(5),
            fn () => [
                'heroSlides' => HeroSlide::active()->get(),
                'heroFallback' => $this->heroFallback(),
                'featuredServices' => Service::query()
                    ->whereNull('deleted_at')
                    ->where('type', ServiceType::UNGGULAN->value)
                    ->limit(6)
                    ->get(),
                'todaySchedules' => $this->schedules
                    ->listFiltered(new ScheduleFilter(day: $this->today())),
                'latestNews' => $this->news->latestForHome(6),
                'galleries' => Gallery::query()
                    ->with(['media' => fn ($q) => $q->orderBy('sort_order')])
                    ->whereHas('media')
                    ->latest()
                    ->take(8)
                    ->get(),
                'totalDoctors' => Doctor::query()->where('is_active', true)->count(),
                'totalPolyclinics' => Polyclinic::query()->where('is_active', true)->count(),
                'totalServices' => Service::query()->whereNull('deleted_at')->count(),
            ]
        );

        return view('public.home.index', $payload);
    }

    private function today(): Day
    {
        return match ((int) now()->dayOfWeekIso) {
            1 => Day::SENIN,
            2 => Day::SELASA,
            3 => Day::RABU,
            4 => Day::KAMIS,
            5 => Day::JUMAT,
            6 => Day::SABTU,
            default => Day::MINGGU,
        };
    }

    /**
     * Hero fallback dari Site_Setting, dipakai saat tidak ada Hero_Slide aktif
     * (Requirement 1.2 / Requirement 35 — hero default).
     *
     * @return array{title:?string,subtitle:?string,image:?string}
     */
    private function heroFallback(): array
    {
        $settings = app(SiteSettingService::class);

        return [
            'title' => $settings->get('hero_default_title')
                ?? $settings->get('rs_name', 'RSUD'),
            'subtitle' => $settings->get('hero_default_subtitle'),
            'image' => $settings->get('hero_default_image'),
        ];
    }
}
