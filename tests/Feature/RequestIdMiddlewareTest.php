<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('attaches an x-request-id header to responses', function (): void {
    $response = $this->postJson('/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ]);

    $response->assertOk();
    expect($response->headers->get('X-Request-Id'))->not->toBeNull();
});

it('propagates a valid inbound request id', function (): void {
    $response = $this->withHeaders(['X-Request-Id' => 'req-12345678'])
        ->postJson('/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ]);

    $response->assertOk();
    expect($response->headers->get('X-Request-Id'))->toBe('req-12345678');
});

it('replaces an invalid inbound request id', function (): void {
    $response = $this->withHeaders(['X-Request-Id' => '!!invalid!!'])
        ->postJson('/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ]);

    $response->assertOk();
    expect($response->headers->get('X-Request-Id'))->not->toBe('!!invalid!!');
});
