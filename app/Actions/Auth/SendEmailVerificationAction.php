<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\SecurityAudit;

final class SendEmailVerificationAction
{
    public function handle(User $user): string
    {
        if ($user->hasVerifiedEmail()) {
            SecurityAudit::log('auth.email_verification.already_verified', [
                'user_id' => $user->id,
            ]);

            return __('api.auth.email_already_verified');
        }

        $user->sendEmailVerificationNotification();

        SecurityAudit::log('auth.email_verification.notification_sent', [
            'user_id' => $user->id,
        ]);

        return __('api.auth.verification_link_sent');
    }
}
