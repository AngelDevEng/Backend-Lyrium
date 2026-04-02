<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DeliveryPartner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'partner_type',
        'ruc',
        'dni',
        'phone',
        'email',
        'vehicle_type',
        'license_plate',
        'license_number',
        'status',
        'rating_average',
        'rating_count',
        'total_deliveries',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'rating_average' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'rating_count' => 'integer',
            'total_deliveries' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }
}
