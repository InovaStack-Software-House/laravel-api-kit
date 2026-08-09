<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('returns the authenticated user for the me endpoint', function (): void {
    $user = User::factory()->create([
        'email' => 'me@example.com',
    ]);

    $token = $user->createToken('test-suite')->plainTextToken;

    $this->withToken($token)
        ->getJson('/v1/auth/me')
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'users')
        ->assertJsonPath('data.attributes.email', 'me@example.com');
});

it('requires sanctum auth for protected routes', function (): void {
    $this->getJson('/v1/auth/me')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('title', 'Unauthenticated');
});

it('returns 403 when the token lacks the required ability', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('limited', ['auth:logout'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/v1/auth/me')
        ->assertStatus(Response::HTTP_FORBIDDEN)
        ->assertJsonPath('title', 'Forbidden');
});
