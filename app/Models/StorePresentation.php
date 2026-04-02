<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StorePresentation extends Model
{
    use HasFactory;

    protected $table = 'store_presentation';

    protected $fillable = [
        'store_id',
        'logo',
        'banner',
        'banner2',
        'description',
        'short_description',
        'layout',
        'theme_color',
        'custom_css',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'seo_keywords' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
