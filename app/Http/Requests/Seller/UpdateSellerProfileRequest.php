<?php

declare(strict_types=1);

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSellerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'username' => ['sometimes', 'string', 'min:3', 'max:30', 'unique:users,username,'.auth()->id()],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'string', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
        ];
    }
}
