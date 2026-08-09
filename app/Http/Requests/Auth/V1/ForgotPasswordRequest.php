<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\V1;

use App\Http\Payloads\Auth\ForgotPasswordPayload;
use Illuminate\Foundation\Http\FormRequest;

final class ForgotPasswordRequest extends FormRequest
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
        ];
    }

    public function payload(): ForgotPasswordPayload
    {
        return new ForgotPasswordPayload(
            email: $this->string('email')->toString(),
        );
    }
}
