<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('categories.manage');
    }

    public function view(User $actor, Category $category): bool
    {
        return $actor->hasPermissionTo('categories.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('categories.manage');
    }

    public function update(User $actor, Category $category): bool
    {
        return $actor->hasPermissionTo('categories.manage');
    }

    public function delete(User $actor, Category $category): bool
    {
        // Physical deletion is forbidden by requirements
        return false;
    }

    public function toggleStatus(User $actor, Category $category): bool
    {
        return $actor->hasPermissionTo('categories.manage');
    }
}
