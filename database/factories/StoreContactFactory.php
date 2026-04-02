<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreContact;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StoreContactFactory extends Factory
{
    protected $model = StoreContact::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'contact_type' => fake()->randomElement(['phone', 'email', 'whatsapp', 'landline']),
            'contact_role' => fake()->randomElement(['primary', 'secondary', 'admin', 'support', 'finance']),
            'value' => fake()->randomElement([
                fake()->phoneNumber(),
                fake()->companyEmail(),
                '+51'.fake()->numerify('#########'),
            ]),
            'label' => null,
            'owner_name' => null,
            'owner_dni' => null,
            'is_verified' => false,
            'is_primary' => fake()->boolean(30),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'contact_role' => 'primary']);
    }

    public function whatsapp(): static
    {
        return $this->state(fn () => ['contact_type' => 'whatsapp', 'value' => '+51'.fake()->numerify('#########')]);
    }

    public function email(): static
    {
        return $this->state(fn () => ['contact_type' => 'email', 'contact_role' => 'primary']);
    }
}
