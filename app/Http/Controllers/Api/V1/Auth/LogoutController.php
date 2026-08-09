<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LogoutUserAction;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Token Authentication')]
#[Endpoint(title: 'Logout', description: 'Revoke the current Sanctum token.')]
#[Authenticated]
#[ScribeResponse(content: null, status: 204, description: 'Token revoked.')]
#[ScribeResponse(content: ['message' => 'Unauthenticated.'], status: 401, description: 'Authentication failed.')]
final class LogoutController
{
    public function __construct(
        private readonly LogoutUserAction $action,
    ) {}

    public function __invoke(#[CurrentUser] User $user): Response
    {
        $this->action->handle(user: $user);

        return response()->noContent();
    }
}
