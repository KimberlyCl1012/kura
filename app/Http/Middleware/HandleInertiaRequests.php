<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $base = (array) parent::share($request);

        $user = $request->user();
        $team = $user?->currentTeam;

        if ($user) {
            $user->loadMissing('detail.site');
        }

        $pivotRole = null;
        if ($user && $team) {
            $pivot = $team->users()->where('user_id', $user->id)->first();
            $pivotRole = $pivot?->pivot?->role;
        }

        $roleKey  = $pivotRole ?: ($team?->name ?: 'guest');
        $roleName = $team?->description
            ?: ($roleKey ? ucwords(str_replace(['-', '_'], ' ', $roleKey)) : 'Guest');

        $permissions = $team ? $team->permissions()->pluck('slug')->values()->all() : [];

        $userSiteId = $user?->site_id ?? $user?->detail?->site_id;
        $userSiteName = $user?->detail?->site?->siteName;

        return array_merge($base, [
            'userRole'        => $roleKey,
            'userRoleName'    => $roleName,
            'userPermissions' => $permissions,
            'userSiteId'      => $userSiteId,
            'userSiteName'    => $userSiteName,
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'current_team_id'   => $user->current_team_id,
                    'current_team'      => $team?->only(['id', 'name', 'description']),
                    'all_teams'         => $user->allTeams()->map->only(['id', 'name', 'description'])->values()->all(),
                    'profile_photo_url' => $user->profile_photo_url ?? null,
                ] : null,
            ],
        ]);
    }
}
