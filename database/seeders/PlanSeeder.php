<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(['slug' => 'emprende'], [
            'name' => 'Emprende',
            'monthly_fee' => 0,
            'commission_rate' => 0.0500,
            'has_membership_fee' => false,
            'features' => ['basic_catalog', 'basic_analytics'],
        ]);

        Plan::firstOrCreate(['slug' => 'crece'], [
            'name' => 'Crece',
            'monthly_fee' => 49.90,
            'commission_rate' => 0.1000,
            'has_membership_fee' => true,
            'features' => ['advanced_catalog', 'advanced_analytics', 'priority_support'],
        ]);

        Plan::firstOrCreate(['slug' => 'especial'], [
            'name' => 'Especial',
            'monthly_fee' => 0,
            'commission_rate' => 0.1500,
            'has_membership_fee' => false,
            'features' => ['full_catalog', 'full_analytics', 'dedicated_support', 'custom_branding'],
        ]);
    }
}
