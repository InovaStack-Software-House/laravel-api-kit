<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\SecurityAudit;

final class LogoutUserAction
{
    public function handle(User $user): void
    {
        $token = $user->currentAccessToken();

        $tokenId = $token->getKey();

        SecurityAudit::log('auth.logout.succeeded', [
            'user_id' => $user->id,
            'token_id' => is_int($tokenId) || is_string($tokenId) ? (string) $tokenId : null,
        ]);

        $token->delete();
    }
}
