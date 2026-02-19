<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        Gate::define('ceo-access', function (User $user) {
            return $user->role === User::ROLE_CEO;
        });

        // Full Access: Create, Edit, Delete Departments - CEO & Director only
        Gate::define('manage-departments', function (User $user) {
            return in_array($user->role, [
                User::ROLE_CEO,
                User::ROLE_DIRECTOR
            ]);
        });

        // Hierarchy Management (Drag & Drop) - CEO, Director, & Manajer
        Gate::define('manage-hierarchy', function (User $user) {
            return in_array($user->role, [
                User::ROLE_CEO,
                User::ROLE_DIRECTOR,
                User::ROLE_MANAJER
            ]);
        });

        // Read-Only Access (View Chart) - All except unauthenticated
        Gate::define('view-org', function (User $user) {
            return in_array($user->role, [
                User::ROLE_CEO,
                User::ROLE_DIRECTOR,
                User::ROLE_MANAJER,
                User::ROLE_SUPERVISOR,
                User::ROLE_STAFF
            ]);
        });
    }
}
