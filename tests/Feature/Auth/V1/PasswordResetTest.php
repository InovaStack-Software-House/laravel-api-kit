<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('sends a password reset link for an existing account', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson('/v1/auth/password/forgot', [
        'email' => $user->email,
    ])->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.password_reset_link_sent'));

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns the same response for unknown emails to prevent enumeration', function (): void {
    Notification::fake();

    $this->postJson('/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ])->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.password_reset_link_sent'));

    Notification::assertNothingSent();
});

it('resets the password with a valid token', function (): void {
    $user = User::factory()->create([
        'password' => 'old-password123',
    ]);

    $token = Password::broker()->createToken($user);

    $this->postJson('/v1/auth/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.password_reset_success'));

    expect($user->fresh()->password)->not->toBe('old-password123');
});

it('returns 422 when the reset token is invalid', function (): void {
    $user = User::factory()->create();

    $this->postJson('/v1/auth/password/reset', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('title', 'Validation Error');
});

it('returns the reset token payload for api clients', function (): void {
    $this->getJson('/v1/auth/password/reset/reset-token-value?email=jane@example.com')
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('token', 'reset-token-value')
        ->assertJsonPath('email', 'jane@example.com');
});
