<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreLegalRepresentative;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StoreLegalRepresentativeFactory extends Factory
{
    protected $model = StoreLegalRepresentative::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'nombre' => fake()->name(),
            'dni' => fake()->numerify('########'),
            'foto_url' => null,
            'direccion_fiscal' => fake()->address(),
            'is_current' => true,
            'valid_from' => now()->toDateString(),
            'valid_until' => null,
        ];
    }
}
