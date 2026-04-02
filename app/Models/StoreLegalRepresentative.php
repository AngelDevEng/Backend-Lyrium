<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StoreLegalRepresentative extends Model
{
    use HasFactory;

    protected $table = 'store_legal_representatives';

    protected $fillable = [
        'store_id',
        'nombre',
        'dni',
        'foto_url',
        'direccion_fiscal',
        'is_current',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
