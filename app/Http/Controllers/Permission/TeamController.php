<?php

namespace App\Http\Controllers\Permission;

use App\Actions\Jetstream\AddTeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Jetstream\Contracts\RemovesTeamMembers;
use Laravel\Jetstream\Jetstream;

class TeamController extends Controller
{
    public function store(Request $request, $teamId)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role'  => ['required', 'string'],
        ]);

        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        app(AddTeamMember::class)->add(
            $request->user(),  // quien agrega (debe tener permiso)
            $team,
            $validated['email'],
            $validated['role'] // <-- rol directo
        );

        return back(303);
    }

    public function update(Request $request, $teamId, $userId)
    {
        // (opcional) si quieres cambiar el rol de un miembro
        app(\Laravel\Jetstream\Actions\UpdateTeamMemberRole::class)->update(
            $request->user(),
            Jetstream::newTeamModel()->findOrFail($teamId),
            $userId,
            $request->validate(['role' => ['required', 'string']])['role']
        );

        return back(303);
    }

    public function destroy(Request $request, $teamId, $userId)
    {
        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        app(RemovesTeamMembers::class)->remove(
            $request->user(),
            $team,
            Jetstream::findUserByIdOrFail($userId)
        );

        return back(303);
    }
}
