<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Payloads\Auth\RegisterUserPayload;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\DatabaseManager;

final class RegisterUserAction
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly IssueApiTokenAction $issueToken,
    ) {}

    /**
     * @return array{user: User, token: string, expires_at: \Carbon\Carbon|null}
     */
    public function handle(RegisterUserPayload $payload): array
    {
        return $this->database->transaction(function () use ($payload): array {
            $user = User::query()->create(
                attributes: $payload->toArray(),
            );

            event(new Registered($user));

            [$token, $expiresAt] = $this->issueToken->handle(
                user: $user,
                deviceName: $payload->deviceName,
            );

            SecurityAudit::log('auth.register.succeeded', [
                'user_id' => $user->id,
                'email_hash' => SecurityAudit::hashEmail($user->email),
                'device_name' => $payload->deviceName,
                'token_expires_at' => $expiresAt?->toAtomString(),
            ]);

            return [
                'user' => $user,
                'token' => $token,
                'expires_at' => $expiresAt,
            ];
        });
    }
}
