<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sticker' => $this->sticker,
            'price' => number_format((float) ($this->sale_price ?? $this->price), 2, '.', ''),
            'regular_price' => $this->regular_price
                ? number_format((float) $this->regular_price, 2, '.', '')
                : number_format((float) $this->price, 2, '.', ''),
            'images' => $this->image ? [
                [
                    'src' => $this->image,
                    'alt' => $this->name,
                ],
            ] : [],
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($cat) => [
                'name' => $cat->name,
            ])->values()->all()),
        ];

        if ($this->type === 'physical') {
            $data['weight'] = $this->weight ? (float) $this->weight : null;
            $data['dimensions'] = $this->dimensions;
            $data['expirationDate'] = $this->expiration_date?->toDateString();
        }

        if ($this->type === 'digital') {
            $data['downloadUrl'] = $this->download_url;
            $data['downloadLimit'] = $this->download_limit;
            $data['fileType'] = $this->file_type;
            $data['fileSize'] = $this->file_size;
        }

        if ($this->type === 'service') {
            $data['serviceDuration'] = $this->service_duration;
            $data['serviceModality'] = $this->service_modality;
            $data['serviceLocation'] = $this->service_location;
        }

        return $data;
    }
}
