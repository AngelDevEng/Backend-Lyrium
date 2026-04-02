<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreContact extends Model
{
    use HasFactory;

    protected $table = 'store_contacts';

    protected $fillable = [
        'store_id',
        'contact_type',
        'contact_role',
        'value',
        'label',
        'owner_name',
        'owner_dni',
        'is_verified',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
