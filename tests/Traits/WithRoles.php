<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Store;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait WithRoles
{
    protected function seedRoles(): void
    {
        foreach (['administrator', 'seller', 'customer', 'logistics_operator'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('administrator');

        return $user;
    }

    protected function createSeller(?Store $store = null): User
    {
        $user = User::factory()->create([
            'is_seller' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('seller');

        if (! $store) {
            Store::factory()->approved()->create(['owner_id' => $user->id]);
        }

        return $user;
    }

    protected function createCustomer(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return $user;
    }
}
