<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\V1;

use App\Http\Payloads\Auth\LoginPayload;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function payload(): LoginPayload
    {
        return new LoginPayload(
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            deviceName: $this->string('device_name', 'default')->toString(),
        );
    }
}
