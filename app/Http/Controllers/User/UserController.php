<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class UserController extends Controller
{
    protected function logChange(array $data): void
    {
        AccessChangeLog::create([
            'user_id'      => auth()->id(),
            'logType'      => $data['logType'] ?? 'Users',
            'table'        => $data['table'] ?? 'users',
            'primaryKey'   => $data['primaryKey'] ?? null,
            'secondaryKey' => $data['secondaryKey'] ?? null,
            'changeType'   => $data['changeType'],
            'fieldName'    => $data['fieldName'] ?? null,
            'oldValue'     => $data['oldValue'] ?? null,
            'newValue'     => $data['newValue'] ?? null,
        ]);
    }

    public function index()
    {
        $users = User::select('id', 'name', 'email')->where('state', 1)->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $this->logChange([
                'changeType' => 'create',
                'primaryKey' => $user->id,
                'newValue'   => json_encode($user->only(['name', 'email']), JSON_UNESCAPED_UNICODE),
            ]);

            $this->logChange([
                'changeType' => 'create',
                'primaryKey' => $user->id,
                'fieldName'  => 'name',
                'newValue'   => (string) $user->name,
            ]);
            $this->logChange([
                'changeType' => 'create',
                'primaryKey' => $user->id,
                'fieldName'  => 'email',
                'newValue'   => (string) $user->email,
            ]);
            $this->logChange([
                'changeType' => 'create',
                'primaryKey' => $user->id,
                'fieldName'  => 'password',
                'newValue'   => '[HASHED]',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente.',
                'data'    => $user,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $camposSimples = ['name', 'email'];
            foreach ($camposSimples as $campo) {
                $old = (string) $user->$campo;
                $new = (string) $request->$campo;
                if ($old !== $new) {
                    $this->logChange([
                        'changeType' => 'update',
                        'primaryKey' => $user->id,
                        'fieldName'  => $campo,
                        'oldValue'   => $old,
                        'newValue'   => $new,
                    ]);
                }
            }

            $passwordChanged = false;
            if ($request->filled('password')) {
                $passwordChanged = true;
                $this->logChange([
                    'changeType' => 'update',
                    'primaryKey' => $user->id,
                    'fieldName'  => 'password',
                    'oldValue'   => '[HASHED]',
                    'newValue'   => '[HASHED]',
                ]);
            }

            $user->name  = $request->name;
            $user->email = $request->email;
            if ($passwordChanged) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data'    => $user,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el usuario.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
