<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('logs in a user and returns a bearer token', function (): void {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
        'device_name' => 'ios-app',
    ])
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonStructure([
            'meta' => ['token', 'token_type', 'expires_at'],
            'data' => ['id', 'type', 'attributes' => ['name', 'email']],
        ])
        ->assertJsonPath('meta.token_type', 'Bearer')
        ->assertJsonPath('data.type', 'users')
        ->assertJsonPath('data.attributes.email', 'john@example.com');

    expect(PersonalAccessToken::query()->count())->toBe(1);
});

it('returns 401 for invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
        'device_name' => 'ios-app',
    ])->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('title', 'Unauthenticated')
        ->assertJsonPath('status', 401);
});

it('returns problem details when validation fails', function (): void {
    $this->postJson('/v1/auth/login', [
        'email' => 'john@example.com',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('title', 'Validation Error')
        ->assertJsonStructure(['type', 'title', 'status', 'detail', 'errors']);
});
