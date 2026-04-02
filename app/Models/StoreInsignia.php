<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreInsignia extends Model
{
    protected $table = 'store_insignias';

    protected $fillable = [
        'store_id',
        'has_premium',
        'status',
        'granted_at',
        'granted_by',
    ];

    protected function casts(): array
    {
        return [
            'has_premium' => 'boolean',
            'granted_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
