<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\V1;

use App\Http\Payloads\Auth\ResetPasswordPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function payload(): ResetPasswordPayload
    {
        return new ResetPasswordPayload(
            token: $this->string('token')->toString(),
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            passwordConfirmation: $this->string('password_confirmation')->toString(),
        );
    }
}
