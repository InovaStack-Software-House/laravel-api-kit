<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Payloads\Auth\LoginPayload;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Hashing\HashManager;

final class LoginUserAction
{
    public function __construct(
        private readonly HashManager $hash,
        private readonly IssueApiTokenAction $issueToken,
    ) {}

    /**
     * @return array{user: User, token: string, expires_at: \Carbon\Carbon|null}
     *
     * @throws AuthenticationException
     */
    public function handle(LoginPayload $payload): array
    {
        $user = User::query()
            ->where('email', $payload->email)
            ->first();

        if (null === $user || ! $this->hash->check($payload->password, $user->password)) {
            SecurityAudit::log('auth.login.failed', [
                'email_hash' => SecurityAudit::hashEmail($payload->email),
                'device_name' => $payload->deviceName,
            ]);

            throw new AuthenticationException();
        }

        [$token, $expiresAt] = $this->issueToken->handle(
            user: $user,
            deviceName: $payload->deviceName,
        );

        SecurityAudit::log('auth.login.succeeded', [
            'user_id' => $user->id,
            'email_hash' => SecurityAudit::hashEmail($payload->email),
            'device_name' => $payload->deviceName,
            'token_expires_at' => $expiresAt?->toAtomString(),
        ]);

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }
}
