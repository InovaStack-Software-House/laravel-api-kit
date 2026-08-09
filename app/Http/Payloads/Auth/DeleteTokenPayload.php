<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

final class DeleteTokenPayload
{
    public function __construct(
        public readonly string $tokenId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
        ];
    }
}
