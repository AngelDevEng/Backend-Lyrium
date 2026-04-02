<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_type' => $this->contact_type,
            'contact_role' => $this->contact_role,
            'value' => $this->value,
            'label' => $this->label,
            'owner_name' => $this->owner_name,
            'owner_dni' => $this->owner_dni,
            'is_verified' => $this->is_verified,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
