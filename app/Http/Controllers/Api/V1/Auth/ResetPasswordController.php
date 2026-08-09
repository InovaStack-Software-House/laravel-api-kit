<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Http\Requests\Auth\V1\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Password Reset')]
#[Endpoint(title: 'Reset Password', description: 'Reset the user password using a valid reset token.')]
#[Unauthenticated]
#[BodyParam('token', type: 'string', description: 'Password reset token.', required: true, example: 'reset-token-value')]
#[BodyParam('email', type: 'string', description: 'Account email associated with token.', required: true, example: 'jane@example.com')]
#[BodyParam('password', type: 'string', description: 'New account password.', required: true, example: 'new-password123')]
#[BodyParam('password_confirmation', type: 'string', description: 'Must match password.', required: true, example: 'new-password123')]
#[Response(content: ['message' => 'Your password has been reset.'], status: 200, description: 'Password reset succeeded.')]
#[Response(
    content: [
        'message' => 'The given data was invalid.',
        'errors' => ['email' => ['This password reset token is invalid.']],
    ],
    status: 422,
    description: 'Reset token or payload was invalid.',
)]
final class ResetPasswordController
{
    public function __construct(
        private readonly ResetPasswordAction $action,
    ) {}

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            payload: $request->payload(),
        );

        return new JsonResponse([
            'message' => __('api.auth.password_reset_success'),
        ]);
    }
}
