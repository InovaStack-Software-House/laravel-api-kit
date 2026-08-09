<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('registers a user and returns a bearer token', function (): void {
    Notification::fake();

    $response = $this->postJson('/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-suite',
    ]);

    $response
        ->assertStatus(Response::HTTP_CREATED)
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonStructure([
            'meta' => ['token', 'token_type', 'expires_at'],
            'data' => ['id', 'type', 'attributes' => ['name', 'email']],
        ])
        ->assertJsonPath('meta.token_type', 'Bearer')
        ->assertJsonPath('data.type', 'users')
        ->assertJsonPath('data.attributes.email', 'jane@example.com');

    $user = User::query()->where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull();
    expect(PersonalAccessToken::query()->count())->toBe(1);
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('issues scoped tokens without wildcard ability', function (): void {
    $response = $this->postJson('/v1/auth/register', [
        'name' => 'Scoped User',
        'email' => 'scoped@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'ios-app',
    ])->assertStatus(Response::HTTP_CREATED);

    $plainToken = (string) $response->json('meta.token');
    $tokenId = explode('|', $plainToken, 2)[0] ?? null;

    expect($tokenId)->not->toBeNull();

    $token = PersonalAccessToken::query()->findOrFail((int) $tokenId);

    expect($token->abilities)->toContain('auth:me');
    expect($token->abilities)->toContain('auth:logout');
    expect($token->abilities)->toContain('auth:verification:send');
    expect($token->abilities)->not->toContain('*');
});

it('returns problem details when validation fails', function (): void {
    $this->postJson('/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('title', 'Validation Error')
        ->assertJsonPath('status', 422)
        ->assertJsonStructure(['type', 'title', 'status', 'detail', 'errors']);
});
