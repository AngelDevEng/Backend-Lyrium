<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'badge_id',
        'is_active',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'earned_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
