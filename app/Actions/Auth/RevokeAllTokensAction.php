<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\SecurityAudit;

final class RevokeAllTokensAction
{
    public function handle(User $user): int
    {
        $deletedCount = $user->tokens()->count();

        $user->tokens()->delete();

        SecurityAudit::log('auth.tokens.revoked_all', [
            'user_id' => $user->id,
            'count' => $deletedCount,
        ]);

        return $deletedCount;
    }
}
