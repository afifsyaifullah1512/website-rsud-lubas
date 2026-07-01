<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\CacheKeys;
use App\Support\Enums\ServiceType;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Listing & detail layanan publik (Requirement 3.1–3.5).
 */
class ServiceController extends Controller
{
    public function __construct(
        private readonly CacheRepository $cache,
    ) {
    }

    public function index(): View
    {
        $grouped = $this->cache->remember(
            CacheKeys::SERVICES_INDEX,
            now()->addMinutes(10),
            static function () {
                return Service::query()
                    ->with('polyclinic')
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->get()
                    ->groupBy(
                        fn (Service $s) => $s->type instanceof ServiceType
                        ? $s->type->value
                        : (string) $s->type
                    );
            }
        );

        return view('public.service.index', [
            'pageTitle' => 'Layanan',
            'grouped' => $grouped,
            'types' => ServiceType::cases(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::query()
            ->with(['polyclinic'])
            ->whereNull('deleted_at')
            ->where('slug', $slug)
            ->first();

        if (! $service) {
            throw new NotFoundHttpException();
        }

        // Dokter di poliklinik terkait (max 6) + layanan lain dengan
        // tipe yang sama (max 4) untuk sidebar.
        $doctors = $service->polyclinic
            ? \App\Models\Doctor::query()
                ->where('polyclinic_id', $service->polyclinic_id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->limit(6)
                ->get(['id', 'name', 'slug', 'specialization', 'photo'])
            : collect();

        $relatedServices = Service::query()
            ->whereNull('deleted_at')
            ->where('id', '!=', $service->id)
            ->where('type', $service->type instanceof ServiceType ? $service->type->value : (string) $service->type)
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'slug', 'name', 'description']);

        return view('public.service.show', [
            'pageTitle' => $service->name,
            'service' => $service,
            'doctors' => $doctors,
            'relatedServices' => $relatedServices,
        ]);
    }
}
