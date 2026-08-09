<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('adds an HSTS header for secure requests', function (): void {
    $response = $this->postJson('https://localhost/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ]);

    $response
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('returns 400 when HTTPS is required but not used', function (): void {
    config(['security.force_https' => true]);

    $this->postJson('/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ])->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJsonPath('title', 'HTTPS Required');
});
