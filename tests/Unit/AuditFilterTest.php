<?php

declare(strict_types=1);

use App\Support\AuditFilter;

/*
|--------------------------------------------------------------------------
| AuditFilter — Unit Tests
|--------------------------------------------------------------------------
|
| Validates: Requirements 15.6, 24.5, 30.7, 32.2.
|
| Memastikan semua key PII di-redact menjadi `[REDACTED]` dan key non-PII
| tetap apa adanya. Test berjalan tanpa boot Laravel agar cepat.
|
*/

it('redacts known PII keys at top level', function () {
    $input = [
        'message' => 'isi pengaduan rahasia',
        'email' => 'pasien@example.com',
        'phone' => '081234567890',
        'ip_address' => '203.0.113.7',
        'password' => 'super-secret',
        'remember_token' => 'token-xyz',
    ];

    $output = AuditFilter::redactPii($input);

    foreach (array_keys($input) as $key) {
        expect($output[$key])->toBe(AuditFilter::REDACTED);
    }
});

it('passes through non-PII keys unchanged', function () {
    $input = [
        'name' => 'Ahmad',
        'subject' => 'Pelayanan IGD',
        'status' => 'NEW',
        'ticket_number' => 'RSUD-20260101-ABC123',
        'id' => 42,
    ];

    expect(AuditFilter::redactPii($input))->toBe($input);
});

it('redacts PII recursively inside nested old/attributes payload', function () {
    $input = [
        'old' => [
            'email' => 'old@example.com',
            'name' => 'Budi',
        ],
        'attributes' => [
            'email' => 'new@example.com',
            'name' => 'Citra',
            'password' => 'plaintext',
        ],
    ];

    $output = AuditFilter::redactPii($input);

    expect($output['old']['email'])->toBe(AuditFilter::REDACTED)
        ->and($output['old']['name'])->toBe('Budi')
        ->and($output['attributes']['email'])->toBe(AuditFilter::REDACTED)
        ->and($output['attributes']['name'])->toBe('Citra')
        ->and($output['attributes']['password'])->toBe(AuditFilter::REDACTED);
});

it('returns empty array for empty input', function () {
    expect(AuditFilter::redactPii([]))->toBe([]);
});
