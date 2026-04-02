<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DeliveryPartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'company_name' => $this->company_name,
            'partner_type' => $this->partner_type,
            'ruc' => $this->ruc,
            'dni' => $this->dni,
            'phone' => $this->phone,
            'email' => $this->email,
            'vehicle_type' => $this->vehicle_type,
            'license_plate' => $this->license_plate,
            'license_number' => $this->license_number,
            'status' => $this->status,
            'rating_average' => (float) $this->rating_average,
            'rating_count' => $this->rating_count,
            'total_deliveries' => $this->total_deliveries,
            'commission_rate' => (float) $this->commission_rate,
            'zones' => $this->whenLoaded('zones', fn () => DeliveryZoneResource::collection($this->zones)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
