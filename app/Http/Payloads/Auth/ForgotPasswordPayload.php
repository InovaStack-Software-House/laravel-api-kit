<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

final class ForgotPasswordPayload
{
    public function __construct(
        public readonly string $email,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
