<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register other policies

        /**
         * Use this code in controller
         * 
         * Example: $this->authorize('create', Service::class);
         * 
         * */
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });



        /**
         * Add wildcard-like support for permissions
         * by this code you can use @canany(['report.*'])
         * like this
         * */
        Gate::before(function ($user, $ability) {
            // Check if the permission has a wildcard (e.g., 'report.*')
            if (str_contains($ability, '*')) {
                // Get the base permission string (e.g., 'report')
                $abilityPrefix = str_replace('.*', '', $ability);

                // Check if the user has any permission that starts with the base string
                return $user->permissions()->where('name', 'like', "$abilityPrefix.%")->exists();
            }
        });
    }
}
