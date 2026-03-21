<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'userId' => $this->owner_id,
            'storeName' => $this->trade_name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'description' => $this->description,
            'email' => $this->corporate_email,
            'phone' => $this->phone,
            'ruc' => $this->ruc,
            'status' => $this->status,
            'sellerType' => $this->seller_type,
            'strikes' => $this->strikes,
            'commissionRate' => (float) $this->commission_rate,
            'totalSales' => 0,
            'totalOrders' => 0,
            'registeredAt' => $this->created_at?->toIso8601String(),
            'verifiedAt' => $this->approved_at?->toIso8601String(),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'subscription' => $this->whenLoaded('subscription'),
        ];
    }
}
