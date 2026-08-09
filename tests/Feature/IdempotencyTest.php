<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('prevents duplicate registration with the same idempotency key', function (): void {
    $key = 'reg-000000000000000000000001';

    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $this->postJson('/v1/auth/register', $payload, ['Idempotency-Key' => $key])
        ->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseCount('users', 1);

    $this->postJson('/v1/auth/register', $payload, ['Idempotency-Key' => $key])
        ->assertStatus(Response::HTTP_CREATED)
        ->assertHeader('Idempotency-Replayed', 'true');

    $this->assertDatabaseCount('users', 1);
});

it('returns 409 when the key is reused with different data', function (): void {
    $key = 'reg-000000000000000000000001';

    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $this->postJson('/v1/auth/register', $payload, ['Idempotency-Key' => $key])
        ->assertStatus(Response::HTTP_CREATED);

    $this->postJson('/v1/auth/register', [
        ...$payload,
        'email' => 'other@example.com',
    ], ['Idempotency-Key' => $key])
        ->assertStatus(Response::HTTP_CONFLICT)
        ->assertJsonPath('title', 'Idempotency Key Conflict');
});

it('returns 422 when the idempotency key format is invalid', function (): void {
    $this->postJson('/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], ['Idempotency-Key' => 'short'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('title', 'Invalid Idempotency Key');
});
