<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Payloads\Auth\ResetPasswordPayload;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ResetPasswordAction
{
    public function __construct(
        private readonly PasswordBroker $passwordBroker,
    ) {}

    public function handle(ResetPasswordPayload $payload): void
    {
        $resetUserId = null;

        $status = $this->passwordBroker->reset(
            credentials: $payload->toArray(),
            callback: function (User $user, string $password) use (&$resetUserId): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUserId = $user->id;

                event(new PasswordReset($user));
            },
        );

        $statusKey = is_string($status) ? $status : PasswordBroker::RESET_THROTTLED;

        if (PasswordBroker::PASSWORD_RESET !== $statusKey) {
            SecurityAudit::log('auth.password_reset.failed', [
                'email_hash' => SecurityAudit::hashEmail($payload->email),
                'status' => $statusKey,
            ]);

            throw ValidationException::withMessages([
                'email' => [__($this->statusToMessageKey($statusKey))],
            ]);
        }

        SecurityAudit::log('auth.password_reset.succeeded', [
            'user_id' => $resetUserId,
            'email_hash' => SecurityAudit::hashEmail($payload->email),
        ]);
    }

    private function statusToMessageKey(string $status): string
    {
        return match ($status) {
            PasswordBroker::INVALID_TOKEN => 'api.auth.password_reset_invalid_token',
            PasswordBroker::INVALID_USER => 'api.auth.password_reset_invalid_user',
            PasswordBroker::RESET_THROTTLED => 'api.auth.password_reset_throttled',
            default => 'api.auth.password_reset_failed',
        };
    }
}
