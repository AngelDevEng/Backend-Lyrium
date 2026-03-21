<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'productId' => $this->product_id,
            'productName' => $this->product_name,
            'unitPrice' => (float) $this->unit_price,
            'quantity' => (int) $this->quantity,
            'lineTotal' => (float) $this->line_total,
            'status' => $this->status,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => (string) $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->getFirstMediaUrl('images') ?? $this->product->image ?? '',
            ]),
            'store' => $this->whenLoaded('store', fn () => [
                'id' => (string) $this->store->id,
                'name' => $this->store->trade_name,
                'slug' => $this->store->slug,
            ]),
        ];
    }
}
