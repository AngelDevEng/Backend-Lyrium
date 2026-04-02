<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DeliveryZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_partner_id',
        'district',
        'city',
        'base_fee',
        'per_km_fee',
        'min_order_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'per_km_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class);
    }
}
