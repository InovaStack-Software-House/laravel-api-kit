<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('revokes the current token on logout', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-suite');

    $this->withToken($token->plainTextToken)
        ->postJson('/v1/auth/logout')
        ->assertStatus(Response::HTTP_NO_CONTENT);

    expect(PersonalAccessToken::query()->whereKey($token->accessToken->id)->exists())->toBeFalse();
});

it('requires authentication for logout', function (): void {
    $this->postJson('/v1/auth/logout')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('title', 'Unauthenticated');
});
