<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\V1;

use App\Http\Payloads\Auth\VerifyEmailPayload;
use Illuminate\Foundation\Http\FormRequest;

final class VerifyEmailRequest extends FormRequest
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
            'id' => ['required', 'string'],
            'hash' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return [
            'id' => (string) $this->route('id'),
            'hash' => (string) $this->route('hash'),
        ];
    }

    public function payload(): VerifyEmailPayload
    {
        return new VerifyEmailPayload(
            id: $this->route('id'),
            hash: $this->route('hash'),
        );
    }
}
