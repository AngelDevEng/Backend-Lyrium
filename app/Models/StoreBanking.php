<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreBanking extends Model
{
    use HasFactory;

    protected $table = 'store_banking';

    protected $fillable = [
        'store_id',
        'bank_code',
        'bank_name',
        'account_type',
        'account_number',
        'cci',
        'currency',
        'holder_name',
        'is_primary',
        'is_verified',
        'purpose',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
