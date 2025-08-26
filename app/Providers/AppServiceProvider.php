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
            'auth' => fn() => [
                'user' => Auth::user() ? [
                    'id'              => Auth::user()->id,
                    'name'            => Auth::user()->name,
                    'email'           => Auth::user()->email,
                    'current_team_id' => Auth::user()->current_team_id,
                    'current_team'    => Auth::user()->currentTeam
                        ? Auth::user()->currentTeam->only(['id', 'name'])
                        : null,
                    'all_teams'       => Auth::user()->allTeams()->map->only(['id', 'name']),
                    'profile_photo_url' => Auth::user()->profile_photo_url ?? null,
                ] : null,
            ],

            'userRole' => function () {
                $user = Auth::user();
                $team = $user?->currentTeam;
                return $team ? optional($user->teamRole($team))->key ?? 'guest' : 'guest';
            },
            'userRoleName' => function () {
                $user = Auth::user();
                $team = $user?->currentTeam;
                return $team ? optional($user->teamRole($team))->name ?? 'Guest' : 'Guest';
            },
            'userPermissions' => function () {
                $user = Auth::user();
                $team = $user?->currentTeam;
                return $team ? array_values((array) $user->teamPermissions($team)) : [];
            },
        ]);
    }
}
