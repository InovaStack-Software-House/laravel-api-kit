<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\VerifyEmailAction;
use App\Http\Requests\Auth\V1\VerifyEmailRequest;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Email Verification')]
#[Endpoint(title: 'Verify Email', description: 'Verify a user email using the signed verification link parameters.')]
#[Unauthenticated]
#[UrlParam('id', type: 'string', description: 'User ULID from the signed verification link.', required: true, example: '01HZX3W3T4J8Q57XNZD5BPHJ92')]
#[UrlParam('hash', type: 'string', description: 'SHA-1 hash of the email from the signed verification link.', required: true, example: '8c4f4370e5db9b5be6d0f4c95495f49f998fa32a')]
#[Response(content: ['message' => 'Email verified successfully.'], status: 200, description: 'Email was verified.')]
#[Response(content: ['message' => 'Invalid verification link.'], status: 403, description: 'Signed URL was invalid, expired, or mismatched.')]
final class VerifyEmailController
{
    public function __construct(
        private readonly VerifyEmailAction $action,
    ) {}

    public function __invoke(VerifyEmailRequest $request): JsonResponse
    {
        return new JsonResponse([
            'message' => $this->action->handle(
                payload: $request->payload(),
            ),
        ]);
    }
}
