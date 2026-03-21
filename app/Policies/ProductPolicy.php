<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

final class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->is_seller || $user->is_admin;
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->is_seller && $product->store?->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->is_seller && $product->store?->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->is_admin;
    }
}
