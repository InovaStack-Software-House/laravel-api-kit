<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Requests\Auth\V1\RegisterRequest;
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
use Symfony\Component\HttpFoundation\Response as HttpStatus;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Token Authentication')]
#[Endpoint(title: 'Register', description: 'Create a new account and issue a Sanctum bearer token.')]
#[Unauthenticated]
#[BodyParam('name', type: 'string', description: 'Display name for the account.', required: true, example: 'Jane Doe')]
#[BodyParam('email', type: 'string', description: 'Unique email address for login.', required: true, example: 'jane@example.com')]
#[BodyParam('password', type: 'string', description: 'Account password.', required: true, example: 'password123')]
#[BodyParam('device_name', type: 'string', description: 'Client device label for token tracking.', required: false, example: 'ios-app')]
#[ResponseFromApiResource(
    name: UserResource::class,
    model: User::class,
    status: 201,
    description: 'Registration succeeded.',
    additional: ['meta' => ['token' => '1|example-token', 'token_type' => 'Bearer', 'expires_at' => null]],
)]
#[Response(
    content: [
        'type' => 'https://example.com/problems/validation-error',
        'title' => 'Validation Error',
        'status' => 422,
        'detail' => 'The given data was invalid.',
    ],
    status: 422,
    description: 'Validation failed.',
)]
final class RegisterController
{
    public function __construct(
        private readonly RegisterUserAction $action,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
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
            ->response()
            ->setStatusCode(HttpStatus::HTTP_CREATED);
    }
}
