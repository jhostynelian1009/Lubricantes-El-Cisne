<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function confirm(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.create') && $sale->isDraft();
    }

    public function receipt(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.create') && $sale->isConfirmed();
    }
}
