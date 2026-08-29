<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermissionTo('inventory.view')
            || $user->hasPermissionTo('products.manage');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin()
            || $user->hasPermissionTo('inventory.view')
            || $user->hasPermissionTo('products.manage');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('products.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('products.manage');
    }

    public function toggleStatus(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('products.manage');
    }

    public function initialStock(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('inventory.adjust');
    }
}
