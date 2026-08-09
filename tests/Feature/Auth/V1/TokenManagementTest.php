<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('lists tokens for the authenticated user', function (): void {
    $user = User::factory()->create();
    $user->createToken('ios-app');
    $user->createToken('cli');
    $token = $user->createToken('current');

    $this->withToken($token->plainTextToken)
        ->getJson('/v1/auth/tokens')
        ->assertStatus(Response::HTTP_OK)
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('meta.count', 3)
        ->assertJsonCount(3, 'data');
});

it('revokes a single token', function (): void {
    $user = User::factory()->create();
    $current = $user->createToken('current');
    $target = $user->createToken('target');

    $this->withToken($current->plainTextToken)
        ->deleteJson("/v1/auth/tokens/{$target->accessToken->id}")
        ->assertStatus(Response::HTTP_NO_CONTENT);

    expect(PersonalAccessToken::query()->whereKey($target->accessToken->id)->exists())->toBeFalse();
    expect(PersonalAccessToken::query()->whereKey($current->accessToken->id)->exists())->toBeTrue();
});

it('returns 404 when revoking a token owned by another user', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $target = $owner->createToken('target');
    $current = $other->createToken('current');

    $this->withToken($current->plainTextToken)
        ->deleteJson("/v1/auth/tokens/{$target->accessToken->id}")
        ->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJsonPath('title', 'Token Not Found');
});

it('revokes all tokens', function (): void {
    $user = User::factory()->create();
    $current = $user->createToken('current');
    $user->createToken('extra');

    $this->withToken($current->plainTextToken)
        ->deleteJson('/v1/auth/tokens')
        ->assertStatus(Response::HTTP_NO_CONTENT);

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(0);
});
