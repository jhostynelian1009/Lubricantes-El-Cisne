<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('suppliers.manage');
    }

    public function view(User $actor, Supplier $supplier): bool
    {
        return $actor->hasPermissionTo('suppliers.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('suppliers.manage');
    }

    public function update(User $actor, Supplier $supplier): bool
    {
        return $actor->hasPermissionTo('suppliers.manage');
    }

    public function delete(User $actor, Supplier $supplier): bool
    {
        // Physical deletion is forbidden by requirements
        return false;
    }

    public function toggleStatus(User $actor, Supplier $supplier): bool
    {
        return $actor->hasPermissionTo('suppliers.manage');
    }
}
