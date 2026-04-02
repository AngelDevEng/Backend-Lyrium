<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PlanSeeder::class,
            BadgeSeeder::class,
            AdminUserSeeder::class,
            PlanRequestSeeder::class,
            CategorySeeder::class,
            HomeSeeder::class,
            LoyaltyAndPaymentSeeder::class,
            ShippingSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
