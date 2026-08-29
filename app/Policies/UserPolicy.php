<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('users.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $actor, User $user): bool
    {
        return $actor->hasPermissionTo('users.manage');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('users.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $actor, User $user): bool
    {
        return $actor->hasPermissionTo('users.manage');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $actor, User $user): bool
    {
        // Physical deletion is forbidden.
        return false;
    }

    /**
     * Determine whether the user can toggle active status of the model.
     */
    public function toggleStatus(User $actor, User $user): bool
    {
        if (!$actor->hasPermissionTo('users.manage')) {
            return false;
        }

        // Cannot deactivate self
        if ($actor->id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reset the password of the model.
     */
    public function resetPassword(User $actor, User $user): bool
    {
        return $actor->hasPermissionTo('users.manage');
    }
}
