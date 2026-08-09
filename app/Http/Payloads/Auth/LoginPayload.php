<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

final class LoginPayload
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $deviceName,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
