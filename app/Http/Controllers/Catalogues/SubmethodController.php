<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SubmethodController extends Controller
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
        $methods = DB::table('list_treatment_methods')
            ->where('state', 1)
            ->get();

        $submethods = DB::table('list_treatment_submethods as sub')
            ->join('list_treatment_methods as ltm', 'ltm.id', '=', 'sub.treatment_method_id')
            ->where('sub.state', 1)
            ->select('sub.*', 'ltm.name as method_name')
            ->get();

        return Inertia::render('Catalogues/SubmethodTreatments/Index', [
            'methods'    => $methods,
            'submethods' => $submethods,
        ]);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'treatment_method_id' => 'required|integer|exists:list_treatment_methods,id',
                'name'                => 'required|string|max:255',
                'description'         => 'nullable|string',
            ]);

            $payload = [
                'treatment_method_id' => (int) $validated['treatment_method_id'],
                'name'                => $validated['name'],
                'description'         => $validated['description'] ?? null,
                'state'               => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $id = DB::table('list_treatment_submethods')->insertGetId($payload);

            $this->logChange([
                'logType'      => 'Submetodo del tratamiento',
                'table'        => 'list_treatment_submethods',
                'primaryKey'   => (string)$id,
                'secondaryKey' => (string)$payload['treatment_method_id'],
                'changeType'   => 'create',
                'newValue'     => json_encode($payload),
            ]);

            $new = DB::table('list_treatment_submethods')->where('id', $id)->first();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submetodo del tratamiento creado correctamente.',
                'data'    => $new, // <- id y treatment_method_id en claro
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


    public function update(Request $request, int $id)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'treatment_method_id' => 'required|integer|exists:list_treatment_methods,id',
                'name'                => 'required|string|max:255',
                'description'         => 'nullable|string',
            ]);

            $old = DB::table('list_treatment_submethods')->where('id', $id)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            $updates = [
                'treatment_method_id' => (int) $validated['treatment_method_id'],
                'name'                => $validated['name'],
                'description'         => $validated['description'] ?? null,
                'updated_at'          => now(),
            ];

            foreach (['treatment_method_id', 'name', 'description'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'      => 'Submetodo del tratamiento',
                        'table'        => 'list_treatment_submethods',
                        'primaryKey'   => (string)$id,
                        'secondaryKey' => (string)$updates['treatment_method_id'],
                        'changeType'   => 'update',
                        'fieldName'    => $campo,
                        'oldValue'     => $viejo,
                        'newValue'     => $nuevo,
                    ]);
                }
            }

            DB::table('list_treatment_submethods')->where('id', $id)->update($updates);
            $subtype = DB::table('list_treatment_submethods')->where('id', $id)->first();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submetodo del tratamiento actualizado correctamente.',
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

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $old = DB::table('list_treatment_submethods')->where('id', $id)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            DB::table('list_treatment_submethods')->where('id', $id)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_treatment_submethods')->where('id', $id)->first();

            $this->logChange([
                'logType'      => 'Submetodo del tratamiento',
                'table'        => 'list_treatment_submethods',
                'primaryKey'   => (string)$id,
                'secondaryKey' => (string)$old->treatment_method_id,
                'changeType'   => 'destroy',
                'oldValue'     => json_encode($old),
                'newValue'     => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submetodo del tratamiento eliminado correctamente.',
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
