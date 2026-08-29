<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin() && $user->isActive()) {
                return true;
            }
        });

        Gate::policy(User::class, UserPolicy::class);

        $permissions = [
            'users.manage',
            'categories.manage',
            'suppliers.manage',
            'customers.manage',
            'products.manage',
            'inventory.view',
            'inventory.entries.create',
            'inventory.adjust',
            'sales.create',
            'sales.cancel',
            'reports.view',
            'reports.export',
            'audit.view',
        ];

        foreach ($permissions as $permissionKey) {
            Gate::define($permissionKey, function (User $user) use ($permissionKey) {
                return $user->hasPermissionTo($permissionKey);
            });
        }
    }
}
