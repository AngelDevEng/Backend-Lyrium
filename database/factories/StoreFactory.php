<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
final class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'owner_id' => User::factory(),
            'ruc' => fake()->numerify('###########'),
            'trade_name' => $name,
            'corporate_email' => fake()->unique()->companyEmail(),
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'description' => fake()->sentence(),
            'status' => 'pending',
            'seller_type' => 'products',
            'commission_rate' => 0.1500,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }

    public function banned(): static
    {
        return $this->state(fn () => [
            'status' => 'banned',
            'banned_at' => now(),
        ]);
    }
}