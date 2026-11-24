<?php

test('swagger ui is reachable', function () {
    $this->get('/api/documentation')
        ->assertOk();
});

test('generated open api document is available', function () {
    $response = $this->get('/docs?api-docs.json');

    $response->assertOk();

    $payload = $response->json();

    expect($payload)->toHaveKeys(['openapi', 'info', 'paths', 'tags'])
        ->and($payload['info']['title'])->toBe('LaGrandeCinema API')
        ->and($payload['info']['version'])->toBe('1.1.0')
        ->and($payload['paths'])->toHaveKeys([
            '/up',
            '/dashboard',
            '/dashboard/films',
            '/dashboard/sessions',
            '/dashboard/rooms',
            '/dashboard/reservations',
            '/dashboard/users',
            '/dashboard/ticket-control',
            '/dashboard/ticket-control/verify',
            '/dashboard/ticket-control/mark-used',
            '/settings/profile',
            '/tickets/{ticket}/qr',
            '/stripe/webhook',
        ]);

    expect(count($payload['paths']))->toBeGreaterThanOrEqual(30);

    $tagNames = collect($payload['tags'])->pluck('name')->all();

    expect($tagNames)->toContain('Administration – Interface Inertia', 'Paramètres utilisateur');
});
