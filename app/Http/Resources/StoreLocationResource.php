<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_type' => $this->location_type,
            'address' => $this->address,
            'district' => $this->district,
            'city' => $this->city,
            'region' => $this->region,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'is_primary' => $this->is_primary,
            'is_pickup_available' => $this->is_pickup_available,
            'operating_hours' => $this->operating_hours,
            'contact_phone' => $this->contact_phone,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
