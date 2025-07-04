<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDeniedPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['deniedPermissions'])
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                $user->denied_permissions = $user->deniedPermissions->map(function ($perm) {
                    return [
                        'id' => $perm->id,
                        'permission' => $perm->permission,
                    ];
                });
                return $user;
            });

        return inertia('Permissions/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        DB::transaction(function () use ($request) {
            UserDeniedPermission::where('user_id', $request->user_id)->delete();

            if (!empty($request->permissions)) {
                foreach ($request->permissions as $permission) {
                    UserDeniedPermission::create([
                        'user_id' => $request->user_id,
                        'permission' => $permission,
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Restricciones actualizadas correctamente.']);
    }

    public function destroy(User $user) {}
}
