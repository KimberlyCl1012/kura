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
        $team = Jetstream::newTeamModel()->findOrFail($teamId);
        $users = User::select('id', 'name', 'email')->where('state', 1)->get();
        $teams = Team::orderBy('name')
            ->get(['id', 'name', 'description']);

        Gate::authorize('view', $team);

        return Jetstream::inertia()->render($request, 'Teams/Show', [
            'team' => $team->load('owner', 'users', 'teamInvitations'),
            'availableRoles' => array_values(Jetstream::$roles),
            'teams' => $teams,
            'users' => $users,
            'availablePermissions' => Jetstream::$permissions,
            'defaultPermissions' => Jetstream::$defaultPermissions,
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                'canDeleteTeam' => Gate::check('delete', $team),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $team),
                'canUpdateTeam' => Gate::check('update', $team),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $team),
            ],
        ]);
    }
}
