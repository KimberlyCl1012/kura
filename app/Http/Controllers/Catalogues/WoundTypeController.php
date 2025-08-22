<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\WoundType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundTypeController extends Controller
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
            ->select(
                'list_wound_types.id',
                'list_wound_types.name',
                'list_wound_types.description',
            )
            ->where('list_wound_types.state', 1)
            ->get()
            ->map(function ($woundsTypes) {
                $woundsTypes->id = Crypt::encryptString($woundsTypes->id);
                return $woundsTypes;
            });

        return Inertia::render('Catalogues/WoundTypes/Index', ([
            'woundsTypes'  =>  $woundsTypes
        ]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $payload = [
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'state'       => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $id = DB::table('list_wound_types')->insertGetId($payload);

            $this->logChange([
                'logType'    => 'Tipo de herida',
                'table'      => 'list_wound_types',
                'primaryKey' => (string)$id,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
            ]);

            $woundType = DB::table('list_wound_types')->where('id', $id)->first();

            DB::commit();

            $woundType->id = Crypt::encryptString($woundType->id);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida creado correctamente.',
                'data'    => $woundType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear tipo de herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el tipo de herida.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $old = DB::table('list_wound_types')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tipo no encontrado.'], 404);
            }

            $updates = [
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'updated_at'  => now(),
            ];

            foreach (['name', 'description'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'    => 'Tipo de herida',
                        'table'      => 'list_wound_types',
                        'primaryKey' => (string)$realId,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            DB::table('list_wound_types')->where('id', $realId)->update($updates);

            $woundType = DB::table('list_wound_types')->where('id', $realId)->first();

            DB::commit();

            $woundType->id = Crypt::encryptString($woundType->id);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida actualizado correctamente.',
                'data'    => $woundType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Actualizar tipo de herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $old = DB::table('list_wound_types')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tipo no encontrado.'], 404);
            }

            DB::table('list_wound_types')->where('id', $realId)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_wound_types')->where('id', $realId)->first();

            $this->logChange([
                'logType'    => 'Tipo de herida',
                'table'      => 'list_wound_types',
                'primaryKey' => (string)$realId,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Eliminar tipo de herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subtypes($woundtypeId)
    {
        $woundType = WoundType::with(['woundSubtypes' => function ($query) {
            $query->where('state', 1)
                ->select('id', 'wound_type_id', 'name');
        }])->findOrFail($woundtypeId);

        return response()->json($woundType->woundSubtypes);
    }
}
