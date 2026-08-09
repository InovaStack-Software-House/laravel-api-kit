<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\PersonalAccessToken;

final class ListTokensAction
{
    /**
     * @return Collection<int, PersonalAccessToken>
     */
    public function handle(User $user): Collection
    {
        return $user->tokens()
            ->latest('id')
            ->get();
    }
}
