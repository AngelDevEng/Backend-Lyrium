<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreSocialMedia extends Model
{
    use HasFactory;

    protected $table = 'store_social_media';

    protected $fillable = [
        'store_id',
        'platform',
        'username',
        'profile_url',
        'followers_count',
        'is_verified',
        'is_primary',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_primary' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
