<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DeliveryZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_partner_id' => $this->delivery_partner_id,
            'district' => $this->district,
            'city' => $this->city,
            'base_fee' => (float) $this->base_fee,
            'per_km_fee' => (float) $this->per_km_fee,
            'min_order_amount' => $this->min_order_amount ? (float) $this->min_order_amount : null,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
