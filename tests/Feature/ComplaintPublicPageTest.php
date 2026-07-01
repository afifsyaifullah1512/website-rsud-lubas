<?php

declare(strict_types=1);

use App\Models\Complaint;
use App\Models\ComplaintLog;
use App\Support\Enums\ComplaintStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Validates: Requirements 11.1, 11.7, 11.8.
 *
 * Memastikan:
 *  - Halaman `/pengaduan` menampilkan form pengaduan (field name, email,
 *    phone, subject, message) — Requirement 11.1.
 *  - Halaman lacak `/pengaduan/cek/{ticket}` menampilkan subjek + status
 *    + timeline TANPA membocorkan PII (email/phone/message/nama) —
 *    Requirement 11.8.
 *  - Ticket yang tidak dikenal menghasilkan 404.
 */
function makeComplaint(array $overrides = []): Complaint
{
    /** @var Complaint $complaint */
    $complaint = Complaint::query()->create(array_merge([
        'ticket_number' => 'RSUD-20240101-ABC123',
        'name' => 'Budi Santoso',
        'email' => 'budi.rahasia@example.com',
        'phone' => '081298765432',
        'subject' => 'Antrean pendaftaran terlalu lama',
        'message' => 'Mohon penambahan loket pendaftaran pada jam sibuk.',
        'status' => ComplaintStatus::NEW,
        'ip_address' => '203.0.113.10',
    ], $overrides));

    ComplaintLog::query()->create([
        'complaint_id' => $complaint->id,
        'user_id' => null,
        'status' => ComplaintStatus::NEW,
        'note' => 'Pengaduan masuk',
    ]);

    return $complaint;
}

it('renders the public complaint form with all required fields (Req 11.1)', function () {
    $this->get('/pengaduan')
        ->assertOk()
        ->assertSee('Pengaduan Masyarakat')
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('name="subject"', false)
        ->assertSee('name="message"', false)
        ->assertSee(route('pengaduan.store'), false);
});

it('shows subject and status timeline on the track page (Req 11.8)', function () {
    $complaint = makeComplaint();

    $this->get(route('pengaduan.track', $complaint->ticket_number))
        ->assertOk()
        ->assertSee('Antrean pendaftaran terlalu lama')
        ->assertSee(ComplaintStatus::NEW->label())
        ->assertSee('Pengaduan masuk');
});

it('does not leak PII on the track page (Req 11.8)', function () {
    $complaint = makeComplaint();

    $response = $this->get(route('pengaduan.track', $complaint->ticket_number))->assertOk();

    $response->assertDontSee('budi.rahasia@example.com');
    $response->assertDontSee('081298765432');
    $response->assertDontSee('Mohon penambahan loket pendaftaran pada jam sibuk.');
    $response->assertDontSee('Budi Santoso');
});

it('returns 404 for an unknown complaint ticket', function () {
    $this->get('/pengaduan/cek/RSUD-20990101-ZZZZZZ')->assertNotFound();
});
