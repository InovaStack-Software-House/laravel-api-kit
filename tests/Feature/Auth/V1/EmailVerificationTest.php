<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

it('sends a verification notification for an unverified user', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $token = $user->createToken('test-suite');

    $this->withToken($token->plainTextToken)
        ->postJson('/v1/auth/email/verification-notification')
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.verification_link_sent'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not resend when already verified', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $token = $user->createToken('test-suite');

    $this->withToken($token->plainTextToken)
        ->postJson('/v1/auth/email/verification-notification')
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.email_already_verified'));

    Notification::assertNothingSent();
});

it('verifies email through the signed link', function (): void {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]);

    $this->getJson($url)
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('message', __('api.auth.email_verified'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('returns 403 for an invalid verification signature', function (): void {
    $user = User::factory()->unverified()->create();

    $this->getJson("/v1/auth/email/verify/{$user->id}/invalid-hash")
        ->assertStatus(Response::HTTP_FORBIDDEN)
        ->assertJsonPath('title', 'Invalid Verification Link');
});
