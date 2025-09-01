<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'team_id' => ['required', 'integer', 'exists:teams,id'],
            ]);

            $user = \App\Models\User::findOrFail((int) $validated['user_id']);
            $team = \App\Models\Team::findOrFail((int) $validated['team_id']);

            \Gate::authorize('addTeamMember', $team);


            \DB::transaction(function () use ($user, $team) {
                \DB::table('team_user')->upsert(
                    [[
                        'user_id'    => $user->id,
                        'team_id'    => $team->id,
                        'role'       => $team->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]],
                    ['user_id'],
                    ['team_id', 'role', 'updated_at']
                );

                // opcional: establecer como equipo actual
                if ($user->current_team_id !== $team->id) {
                    $user->switchTeam($team);
                }
            });

            return back()->with('flash.banner', 'Miembro asignado/actualizado correctamente.');
        } catch (\Throwable $e) {
            \Log::error('Error al asignar miembro al equipo', [
                'request' => $request->only(['user_id', 'team_id', 'role']),
                'error'   => $e->getMessage(),
            ]);

            return back()
                ->with('flash.bannerStyle', 'danger')
                ->with('flash.banner', 'Ocurrió un error al asignar el miembro.');
        }
    }

    public function show(Request $request, $teamId)
    {
        $team = Team::with([
            'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.email');
            },
            'owner',
            'teamInvitations'
        ])->findOrFail($teamId);

        Gate::authorize('view', $team);

        $users = User::select('id', 'name', 'email')
            ->where('state', 1)
            ->orderBy('name')
            ->get();

        $teams = Team::orderBy('name')
            ->get(['id', 'name', 'description']);

        $usersAll = User::with([
            'teams' => fn($q) => $q->select('teams.id', 'teams.name', 'teams.description')
        ])->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $roleMap = collect(\Laravel\Jetstream\Jetstream::$roles)
            ->mapWithKeys(fn($r) => [$r->key => $r->name])
            ->all();

        $allUsersWithTeams = $usersAll->map(function ($u) use ($roleMap) {
            return [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'teams' => $u->teams->map(function ($t) use ($roleMap) {
                    $roleKey  = $t->membership->role ?? null;
                    $roleName = $roleKey ? ($roleMap[$roleKey] ?? $roleKey) : '—';
                    return [
                        'id'              => $t->id,
                        'name'            => $t->name,
                        'description'     => $t->description,
                        'role_key'        => $roleKey,
                        'role_name'       => $roleName,
                    ];
                })->values(),
            ];
        })->values();

        $allUserTeamRows = [];
        foreach ($usersAll as $u) {
            foreach ($u->teams as $t) {
                $roleKey  = $t->membership->role ?? null;
                $allUserTeamRows[] = [
                    'user_id'          => $u->id,
                    'user_name'        => $u->name,
                    'user_email'       => $u->email,
                    'team_id'          => $t->id,
                    'team_name'        => $t->name,
                    'team_description' => $t->description,
                    'role_key'         => $roleKey,
                    'role_name'        => $roleKey ? ($roleMap[$roleKey] ?? $roleKey) : '—',
                ];
            }
        }

        return \Laravel\Jetstream\Jetstream::inertia()->render($request, 'Teams/Show', [
            'team'                 => $team,
            'availableRoles'       => array_values(\Laravel\Jetstream\Jetstream::$roles),
            'teams'                => $teams,
            'users'                => $users,
            'availablePermissions' => \Laravel\Jetstream\Jetstream::$permissions,
            'defaultPermissions'   => \Laravel\Jetstream\Jetstream::$defaultPermissions,
            'permissions'          => [
                'canAddTeamMembers'    => Gate::check('addTeamMember', $team),
                'canDeleteTeam'        => Gate::check('delete', $team),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $team),
                'canUpdateTeam'        => Gate::check('update', $team),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $team),
            ],
            'allUsersWithTeams'    => $allUsersWithTeams,
            'allUserTeamRows'      => $allUserTeamRows,
        ]);
    }
}
