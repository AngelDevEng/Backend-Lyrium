<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'type',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'weight',
        'dimensions',
        'image',
        'sticker',
        'discount_percentage',
        'status',
        'expiration_date',
        // Digital
        'download_url',
        'download_limit',
        'file_type',
        'file_size',
        // Service
        'service_duration',
        'service_modality',
        'service_location',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'weight' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'expiration_date' => 'date',
            'download_limit' => 'integer',
            'file_size' => 'integer',
            'service_duration' => 'integer',
        ];
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function mainAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->where('type', 'main');
    }

    public function additionalAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->where('type', 'additional');
    }

    public function decrementStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }
}
