<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

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
        Inertia::share([
            'userRole' => function () {
                $user = Auth::user();
                return $user ? $user->current_team_role_name ?? 'guest' : 'guest';
            },
            'userPermissions' => function () {
                $user = Auth::user();
                return $user ? $user->current_team_role_permissions ?? [] : [];
            },
            'deniedPermissions' => function () {
                $user = Auth::user();
                if (!$user) return [];

                return $user->deniedPermissions()->pluck('permission')->toArray(); // trae todos los denegados
            },
        ]);
    }
}
