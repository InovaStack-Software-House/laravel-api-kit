<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Requests\Auth\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Token Authentication')]
#[Endpoint(title: 'Login', description: 'Authenticate a user and issue a Sanctum bearer token.')]
#[Unauthenticated]
#[BodyParam('email', type: 'string', description: 'User email address.', required: true, example: 'jane@example.com')]
#[BodyParam('password', type: 'string', description: 'User password.', required: true, example: 'password123')]
#[BodyParam('device_name', type: 'string', description: 'Client device label for token tracking.', required: true, example: 'ios-app')]
#[ResponseFromApiResource(
    name: UserResource::class,
    model: User::class,
    status: 200,
    description: 'Login succeeded.',
    additional: ['meta' => ['token' => '1|example-token', 'token_type' => 'Bearer', 'expires_at' => null]],
)]
#[Response(
    content: [
        'type' => 'https://example.com/problems/unauthenticated',
        'title' => 'Unauthenticated',
        'status' => 401,
        'detail' => 'You are not authenticated.',
    ],
    status: 401,
    description: 'Credentials were invalid.',
)]
final class LoginController
{
    public function __construct(
        private readonly LoginUserAction $action,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        [
            'user' => $user,
            'token' => $token,
            'expires_at' => $expiresAt,
        ] = $this->action->handle(
            payload: $request->payload(),
        );

        return UserResource::make($user)
            ->additional([
                'meta' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt?->toAtomString(),
                ],
            ])
            ->response();
    }
}
