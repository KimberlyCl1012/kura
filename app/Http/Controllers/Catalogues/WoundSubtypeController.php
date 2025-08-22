<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\WoundSubtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WoundSubtypeController extends Controller
{
    protected function logChange(array $data)
    {
        AccessChangeLog::create([
            'user_id'      => auth()->id(),
            'logType'      => $data['logType'],
            'table'        => $data['table'],
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
        $woundsTypes = DB::table('list_wound_types')
            ->where('state', 1)
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                return $item;
            });

        $woundsSubtypes = DB::table('list_wound_subtypes as sub')
            ->join('list_wound_types as wt', 'wt.id', '=', 'sub.wound_type_id')
            ->where('sub.state', 1)
            ->select('sub.*', 'wt.name as wound_type_name')
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                $item->wound_type_id = Crypt::encryptString($item->wound_type_id);
                return $item;
            });

        return Inertia::render('Catalogues/WoundSubtypes/Index', [
            'woundsTypes' => $woundsTypes,
            'woundsSubtypes' => $woundsSubtypes,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $realWoundTypeId = Crypt::decryptString($request->wound_type_id);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $payload = [
                'wound_type_id' => $realWoundTypeId,
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'state'         => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            $id = DB::table('list_wound_subtypes')->insertGetId($payload);

            $this->logChange([
                'logType'      => 'Subtipo de herida',
                'table'        => 'list_wound_subtypes',
                'primaryKey'   => (string)$id,
                'secondaryKey' => (string)$realWoundTypeId,
                'changeType'   => 'create',
                'newValue'     => json_encode($payload),
            ]);

            $subtype = DB::table('list_wound_subtypes')->where('id', $id)->first();

            DB::commit();

            $subtype->id = Crypt::encryptString($subtype->id);
            $subtype->wound_type_id = $request->wound_type_id;

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida creado correctamente.',
                'data'    => $subtype,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el subtipo.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);
            $realWoundTypeId = Crypt::decryptString($request->wound_type_id);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $old = DB::table('list_wound_subtypes')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            $updates = [
                'wound_type_id' => $realWoundTypeId,
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'updated_at'    => now(),
            ];

            foreach (['wound_type_id', 'name', 'description'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'      => 'Subtipo de herida',
                        'table'        => 'list_wound_subtypes',
                        'primaryKey'   => (string)$realId,
                        'secondaryKey' => (string)$realWoundTypeId,
                        'changeType'   => 'update',
                        'fieldName'    => $campo,
                        'oldValue'     => $viejo,
                        'newValue'     => $nuevo,
                    ]);
                }
            }

            DB::table('list_wound_subtypes')->where('id', $realId)->update($updates);

            $subtype = DB::table('list_wound_subtypes')->where('id', $realId)->first();

            DB::commit();

            $subtype->id = Crypt::encryptString($subtype->id);
            $subtype->wound_type_id = $request->wound_type_id;

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida actualizado correctamente.',
                'data'    => $subtype,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el subtipo.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $old = DB::table('list_wound_subtypes')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            DB::table('list_wound_subtypes')->where('id', $realId)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_wound_subtypes')->where('id', $realId)->first();

            $this->logChange([
                'logType'      => 'Subtipo de herida',
                'table'        => 'list_wound_subtypes',
                'primaryKey'   => (string)$realId,
                'secondaryKey' => (string)$old->wound_type_id,
                'changeType'   => 'destroy',
                'oldValue'     => json_encode($old),
                'newValue'     => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida eliminado correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el subtipo.',
            ], 500);
        }
    }
}
