<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Http\Payloads\Auth\VerifyEmailPayload;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\Verified;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class VerifyEmailAction
{
    public function handle(VerifyEmailPayload $payload): string
    {
        $user = User::query()->findOrFail($payload->id);

        if ( ! hash_equals(sha1($user->getEmailForVerification()), $payload->hash)) {
            SecurityAudit::log('auth.email_verification.failed', [
                'user_id' => $user->id,
                'reason' => 'hash_mismatch',
            ]);

            throw new AccessDeniedHttpException(__('api.auth.invalid_verification_link'));
        }

        $wasAlreadyVerified = $user->hasVerifiedEmail();

        if ( ! $wasAlreadyVerified && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        SecurityAudit::log('auth.email_verification.succeeded', [
            'user_id' => $user->id,
            'already_verified' => $wasAlreadyVerified,
        ]);

        return __('api.auth.email_verified');
    }
}
