<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\SecurityAudit;

final class RevokeTokenAction
{
    public function handle(User $user, string $tokenId): bool
    {
        $token = $user->tokens()
            ->whereKey($tokenId)
            ->first();

        if (null === $token) {
            SecurityAudit::log('auth.tokens.revoke_failed', [
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'reason' => 'not_found',
            ]);

            return false;
        }

        $tokenKey = $token->getKey();

        SecurityAudit::log('auth.tokens.revoked', [
            'user_id' => $user->id,
            'token_id' => is_int($tokenKey) || is_string($tokenKey) ? (string) $tokenKey : null,
        ]);

        $token->delete();

        return true;
    }
}
