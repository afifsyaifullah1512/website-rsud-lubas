<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Support\ValueObjects\ComplaintData;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Form pengaduan publik (Requirement 11.1–11.10).
 */
class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintService $service,
    ) {
    }

    public function create(): View
    {
        return view('public.complaint.create', [
            'pageTitle' => 'Pengaduan Masyarakat',
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $data = ComplaintData::fromRequest($request->validated());

        try {
            $complaint = $this->service->submit(
                $data,
                (string) $request->ip(),
            );
        } catch (ThrottleRequestsException $e) {
            return back()
                ->withInput($request->safe()->except(['message']))
                ->withErrors([
                    'message' => 'Terlalu banyak pengaduan dari IP ini. Silakan coba lagi nanti.',
                ]);
        }

        return redirect()
            ->route('pengaduan.thanks', ['ticket' => $complaint->ticket_number])
            ->with('status', 'Pengaduan terkirim. Tiket Anda: '.$complaint->ticket_number);
    }

    public function thanks(string $ticket): View
    {
        $complaint = Complaint::query()
            ->where('ticket_number', $ticket)
            ->first();

        if (! $complaint) {
            throw new NotFoundHttpException();
        }

        return view('public.complaint.thanks', [
            'pageTitle' => 'Pengaduan Terkirim',
            'complaint' => $complaint,
        ]);
    }

    public function track(string $ticket): View
    {
        $complaint = Complaint::query()
            ->with(['logs' => fn ($q) => $q->orderBy('created_at')])
            ->where('ticket_number', $ticket)
            ->first();

        if (! $complaint) {
            throw new NotFoundHttpException();
        }

        return view('public.complaint.track', [
            'pageTitle' => 'Lacak Pengaduan',
            'complaint' => $complaint,
        ]);
    }
}
