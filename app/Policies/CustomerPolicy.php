<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('customers.manage');
    }

    public function view(User $actor, Customer $customer): bool
    {
        return $actor->hasPermissionTo('customers.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('customers.manage');
    }

    public function update(User $actor, Customer $customer): bool
    {
        return $actor->hasPermissionTo('customers.manage');
    }

    public function delete(User $actor, Customer $customer): bool
    {
        // Physical deletion is forbidden by requirements
        return false;
    }

    public function toggleStatus(User $actor, Customer $customer): bool
    {
        return $actor->hasPermissionTo('customers.manage');
    }
}
