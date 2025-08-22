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

        $roleName = $user?->current_team_role_name ?? 'guest';
        $perms    = $user?->current_team_role_permissions ?? [];

        $team     = $user?->currentTeam;
        $roleKey  = $team ? optional($user->teamRole($team))->key : 'guest';

        return array_merge($base, [
            'userRole'        => fn() => $roleKey,
            'userRoleName'    => fn() => $roleName,
            'userPermissions' => fn() => (array) $perms
        ]);
    }
}
