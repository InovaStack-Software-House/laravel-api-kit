<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RevokeTokenAction;
use App\Http\Requests\Auth\V1\DeleteTokenRequest;
use App\Http\Responses\ProblemResponse;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Token Authentication')]
#[Endpoint(title: 'Revoke Token', description: 'Revoke one personal access token owned by the authenticated user.')]
#[Authenticated]
#[UrlParam('token_id', type: 'integer', description: 'Personal access token id.', required: true, example: 42)]
#[ScribeResponse(content: null, status: 204, description: 'Token revoked.')]
#[ScribeResponse(content: ['message' => 'Token not found.'], status: 404, description: 'Token does not belong to current user or no longer exists.')]
#[ScribeResponse(content: ['message' => 'Forbidden.'], status: 403, description: 'Token is missing required ability.')]
#[ScribeResponse(content: ['message' => 'Unauthenticated.'], status: 401, description: 'Authentication failed.')]
final class DeleteTokenController
{
    public function __construct(
        private readonly RevokeTokenAction $action,
    ) {}

    public function __invoke(DeleteTokenRequest $request, #[CurrentUser] User $user): Responsable|Response
    {
        $revoked = $this->action->handle(
            user: $user,
            tokenId: $request->payload()->tokenId,
        );

        if ( ! $revoked) {
            return new ProblemResponse(
                type: 'https://example.com/problems/token-not-found',
                title: 'Token Not Found',
                status: Response::HTTP_NOT_FOUND,
                detail: __('api.auth.token_not_found'),
            );
        }

        return response()->noContent();
    }
}
