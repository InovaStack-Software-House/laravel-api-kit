<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Support\SecurityAudit;
use Illuminate\Contracts\Auth\PasswordBroker;

final class SendPasswordResetLinkAction
{
    public function __construct(
        private readonly PasswordBroker $passwordBroker,
    ) {}

    public function handle(string $email): void
    {
        $status = $this->passwordBroker->sendResetLink([
            'email' => $email,
        ]);

        SecurityAudit::log('auth.password_reset.requested', [
            'email_hash' => SecurityAudit::hashEmail($email),
            'status' => $status,
        ]);
    }
}
