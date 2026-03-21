<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'nicename',
        'avatar',
        'phone',
        'document_type',
        'document_number',
        'is_seller',
        'is_admin',
        'password',
        'email_verified_at',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_seller' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    // Tiendas que este usuario posee
    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    // Tiendas donde es miembro (staff/manager)
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Obtener la tienda principal (primera aprobada)
    public function getStoreAttribute(): ?Store
    {
        if ($this->relationLoaded('ownedStores')) {
            $stores = $this->ownedStores;
            return $stores->firstWhere('status', 'approved') ?? $stores->first();
        }

        return $this->ownedStores()->where('status', 'approved')->first()
            ?? $this->ownedStores()->first();
    }

    // Rol para el frontend (administrator, seller, customer, logistics_operator)
    public function getFrontendRoleAttribute(): string
    {
        if ($this->is_admin) return 'administrator';
        if ($this->is_seller) return 'seller';
        if ($this->hasRole('logistics_operator')) return 'logistics_operator';
        return 'customer';
    }
}
