<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\WoundPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundPhaseController extends Controller
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
        $woundsPhases = DB::table('list_wound_phases')
            ->select('id', 'name', 'description')
            ->where('state', 1)
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                return $item;
            });

        return Inertia::render('Catalogues/WoundPhases/Index', [
            'woundsPhases' => $woundsPhases
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $payload = [
                'name'        => $request->name,
                'description' => $request->description,
                'state'       => 1,
            ];

            $phase = WoundPhase::create($payload);

            $this->logChange([
                'logType'    => 'Fase de herida',
                'table'      => 'list_wound_phases',
                'primaryKey' => (string)$phase->id,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fase creada exitosamente',
                'data' => [
                    'id' => Crypt::encryptString($phase->id),
                    'name' => $phase->name,
                    'description' => $phase->description,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear fase de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la fase.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $phase = WoundPhase::findOrFail($realId);

            $old = $phase->only(['name', 'description']);

            $updates = [
                'name'        => $request->name,
                'description' => $request->description,
            ];

            foreach (['name', 'description'] as $campo) {
                $viejo = $old[$campo];
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'    => 'Fase de herida',
                        'table'      => 'list_wound_phases',
                        'primaryKey' => (string)$realId,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            $phase->update($updates);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fase actualizada correctamente',
                'data' => [
                    'id' => Crypt::encryptString($phase->id),
                    'name' => $phase->name,
                    'description' => $phase->description,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Editar fase de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $phase = WoundPhase::findOrFail($realId);

            $old = $phase->toArray();

            $phase->update(['state' => 0]);

            $new = $phase->fresh()->toArray();

            $this->logChange([
                'logType'    => 'Fase de herida',
                'table'      => 'list_wound_phases',
                'primaryKey' => (string)$realId,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fase eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Eliminar fase de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
