<?php

namespace App\Policies;

use App\Enums\StockEntryStatus;
use App\Models\StockEntry;
use App\Models\User;

class StockEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, StockEntry $stockEntry): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.entries.create');
    }

    public function update(User $user, StockEntry $stockEntry): bool
    {
        return $user->hasPermissionTo('inventory.entries.create') && $stockEntry->status === StockEntryStatus::DRAFT;
    }

    public function delete(User $user, StockEntry $stockEntry): bool
    {
        return $user->hasPermissionTo('inventory.entries.create') && $stockEntry->status === StockEntryStatus::DRAFT;
    }

    public function confirm(User $user, StockEntry $stockEntry): bool
    {
        return $user->hasPermissionTo('inventory.entries.create') && $stockEntry->status === StockEntryStatus::DRAFT;
    }
}
