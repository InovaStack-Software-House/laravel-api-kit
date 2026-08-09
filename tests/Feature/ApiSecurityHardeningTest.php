<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('includes baseline hardening headers on api responses', function (): void {
    $this->postJson('/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ])
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Request-Id');
});

it('rejects non-json write payloads with 415', function (): void {
    $this->post('/v1/auth/register', [
        'email' => 'test@example.com',
    ], [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Accept' => 'application/json',
    ])->assertStatus(Response::HTTP_UNSUPPORTED_MEDIA_TYPE)
        ->assertJsonPath('title', 'Unsupported Media Type');
});
