<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreLocation extends Model
{
    use HasFactory;

    protected $table = 'store_locations';

    protected $fillable = [
        'store_id',
        'location_type',
        'address',
        'district',
        'city',
        'region',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_primary',
        'is_pickup_available',
        'operating_hours',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_pickup_available' => 'boolean',
            'operating_hours' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
