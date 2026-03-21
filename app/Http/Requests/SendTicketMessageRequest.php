<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendTicketMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:5000'],
            'type' => ['sometimes', 'in:normal,quick_reply,escalation,system'],
        ];
    }
}
