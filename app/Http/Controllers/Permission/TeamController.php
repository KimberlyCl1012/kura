<?php

namespace App\Http\Controllers\Permission;

use App\Actions\Jetstream\AddTeamMember;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Validation\Rule;
use App\Actions\Jetstream\UpdateTeamMemberRole;
use App\Actions\Jetstream\RemoveTeamMember;

class TeamController extends Controller
{
    public function index(Request $request, Team $team)
    {
        $this->authorize('view', $team);

        $members = $team->allUsers()->map(function ($u) use ($team) {
            $role = $u->teamRole($team);
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $role?->key,
                'role_name' => $role?->name,
            ];
        });

        $roles = collect(Jetstream::$roles)->values()->map(fn($r) => [
            'key' => $r->key,
            'name' => $r->name,
            'permissions' => $r->permissions
        ]);

        return inertia('Permissions/Team', [
            'team' => $team,
            'members' => $members,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request, Team $team, AddTeamMember $adder)
    {
        $this->authorize('addTeamMember', $team);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['nullable', Rule::in(array_keys(config('roles', [])))],
        ]);

        // Importante: AddTeamMember solo admite usuarios existentes.
        // Si no existe, devuelve error de validación.
        $adder->add($request->user(), $team, $data['email'], $data['role'] ?? null);

        return back()->with('flash.banner', 'Usuario agregado al equipo.');
    }

    public function updateRole(Request $request, Team $team, User $user, UpdateTeamMemberRole $upd)
    {
        $this->authorize('updateTeamMember', $team);

        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('roles', [])))],
        ]);

        $upd->update($request->user(), $team, $user->id, $data['role']);

        return back()->with('flash.banner', 'Rol actualizado.');
    }

    public function destroy(Request $request, Team $team, User $user, RemoveTeamMember $rm)
    {
        $this->authorize('removeTeamMember', $team);
        $rm->remove($request->user(), $team, $user);
        return back()->with('flash.banner', 'Miembro eliminado.');
    }
}
