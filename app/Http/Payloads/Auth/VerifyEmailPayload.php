<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

final class VerifyEmailPayload
{
    public function __construct(
        public readonly string $id,
        public readonly string $hash,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'hash' => $this->hash,
        ];
    }
}
