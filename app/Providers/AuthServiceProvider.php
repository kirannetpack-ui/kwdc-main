<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for different roles
        Gate::define('admin', function ($user) {
            return $user->is_admin || $user->role === 'admin';
        });

        Gate::define('client', function ($user) {
            return $user->is_client || $user->role === 'client';
        });

        Gate::define('driver', function ($user) {
            return $user->is_driver || $user->role === 'driver';
        });

        Gate::define('equipment-owner', function ($user) {
            return $user->is_equipment_owner || $user->role === 'equipment_owner';
        });

        Gate::define('property-owner', function ($user) {
            return $user->is_property_owner || $user->role === 'property_owner';
        });
    }
}