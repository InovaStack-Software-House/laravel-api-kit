<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\V1;

use App\Http\Payloads\Auth\DeleteTokenPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DeleteTokenRequest extends FormRequest
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
            'token_id' => ['required', 'integer', Rule::exists('personal_access_tokens', 'id')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return [
            'token_id' => $this->route('token_id'),
        ];
    }

    public function payload(): DeleteTokenPayload
    {
        return new DeleteTokenPayload(
            tokenId: (string) $this->route('token_id'),
        );
    }
}
