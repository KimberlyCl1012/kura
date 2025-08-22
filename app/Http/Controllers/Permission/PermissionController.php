<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Permission;
use App\Models\Team;
use App\Models\TeamPermission;
use App\Models\User;
use App\Providers\JetstreamServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PermissionController extends Controller
{
    protected function logChange(array $data): void
    {
        AccessChangeLog::create([
            'user_id'      => auth()->id(),
            'logType'      => $data['logType'] ?? 'Permissions',
            'table'        => $data['table'] ?? 'team_permission',
            'primaryKey'   => $data['primaryKey'] ?? null,
            'secondaryKey' => $data['secondaryKey'] ?? null,
            'changeType'   => $data['changeType'],
            'fieldName'    => $data['fieldName'] ?? null,
            'oldValue'     => $data['oldValue'] ?? null,
            'newValue'     => $data['newValue'] ?? null,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $currentTeam = $user->currentTeam;

        abort_unless($currentTeam, 403);

        $teams = Team::orderBy('name')->get(['id', 'name', 'description']);

        $selectedTeam = $teams->firstWhere('id', $currentTeam->id) ?? $teams->first();

        $all = Permission::orderBy('name')->get(['id', 'name', 'slug', 'description', 'state']);

        $enabledIds = $selectedTeam
            ? $selectedTeam->permissions()->pluck('permissions.id')->all()
            : [];

        $rows = $all->map(fn($p) => [
            'id'              => $p->id,
            'name'            => $p->name,
            'slug'            => $p->slug,
            'description'     => $p->description,
            'state'           => (bool) $p->state,
            'enabled_in_team' => in_array($p->id, $enabledIds, true),
        ])->values()->all();

        return Inertia::render('Permissions/Index', [
            'teams'            => $teams->values()->all(),
            'selectedTeamId'   => $selectedTeam?->id,
            'permissions'      => $rows,
            'enabledIds'       => $enabledIds,
            'canEdit'          => $user->ownsTeam($selectedTeam),
        ]);
    }

    public function show(Request $request, Team $team)
    {
        $user = $request->user();
        abort_unless($team && $user->belongsToTeam($team), 403);

        return response()->json([
            'enabledIds' => $team->permissions()->pluck('permissions.id')->all(),
            'canEdit'    => $user->ownsTeam($team),
        ]);
    }

    public function sync(Request $request, Team $team)
    {
        $user = $request->user();
        abort_unless($team && $user->ownsTeam($team), 403);

        $data = $request->validate([
            'permission_ids'   => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $before = $team->permissions()->pluck('permissions.id')->all();
        $after  = array_values(array_unique($data['permission_ids'] ?? []));

        sort($before);
        sort($after);

        // Deltas
        $added   = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        DB::beginTransaction();
        try {
            $team->permissions()->sync($after);

            $this->logChange([
                'changeType' => 'sync',
                'primaryKey' => $team->id,
                'oldValue'   => json_encode(['enabledIds' => $before], JSON_UNESCAPED_UNICODE),
                'newValue'   => json_encode(['enabledIds' => $after],  JSON_UNESCAPED_UNICODE),
            ]);

            foreach ($added as $pid) {
                $this->logChange([
                    'changeType'   => 'update',
                    'primaryKey'   => $team->id,
                    'secondaryKey' => $pid,
                    'fieldName'    => 'permission',
                    'oldValue'     => null,
                    'newValue'     => (string) $pid,
                ]);
            }

            foreach ($removed as $pid) {
                $this->logChange([
                    'changeType'   => 'detach',
                    'primaryKey'   => $team->id,
                    'secondaryKey' => $pid,
                    'fieldName'    => 'permission',
                    'oldValue'     => (string) $pid,
                    'newValue'     => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message'     => 'Permisos actualizados.',
                'enabledIds'  => $team->permissions()->pluck('permissions.id')->all(),
                'added'       => $added,
                'removed'     => $removed,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['error' => 'No fue posible sincronizar los permisos.'], 500);
        }
    }
}
