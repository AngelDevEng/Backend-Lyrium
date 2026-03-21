<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'ruc',
        'trade_name',
        'corporate_email',
        'slug',
        'description',
        'logo',
        'banner',
        'phone',
        'status',
        'seller_type',
        'strikes',
        'commission_rate',
        'approved_at',
        'banned_at',
    ];

    protected function casts(): array
    {
        return [
            'strikes' => 'integer',
            'commission_rate' => 'decimal:4',
            'approved_at' => 'datetime',
            'banned_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_store')->withTimestamps();
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    public function addStrike(): void
    {
        $this->increment('strikes');
        if ($this->strikes >= 3) {
            $this->update([
                'status' => 'banned',
                'banned_at' => now(),
            ]);
        }
    }
}
