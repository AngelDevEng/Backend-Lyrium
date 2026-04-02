<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreBankingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'account_type' => $this->account_type,
            'account_number' => $this->account_number,
            'cci' => $this->cci,
            'currency' => $this->currency,
            'holder_name' => $this->holder_name,
            'is_primary' => $this->is_primary,
            'is_verified' => $this->is_verified,
            'purpose' => $this->purpose,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
