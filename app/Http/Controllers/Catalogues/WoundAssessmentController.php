<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\WoundAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WoundAssessmentController extends Controller
{
    protected function logChange(array $data): void
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

    public function index(Request $request)
    {
        $assessments = DB::table('list_wound_assessments')->get();
        $types = DB::table('list_wound_assessments')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->get()
            ->map(fn($r) => ['label' => $r->type, 'value' => $r->type])
            ->values();

        return Inertia::render('Catalogues/WoundAssessments/Index', [
            'assessments' => $assessments,
            'types' => $types,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'type'        => ['required', 'string', 'max:100'],
                'name'        => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('list_wound_assessments')->where(fn($q) => $q->where('type', $request->type))
                ],
                'description' => ['nullable', 'string'],
                'state'       => ['nullable', 'boolean'],
            ]);

            $payload = [
                'type'        => $validated['type'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'state'       => array_key_exists('state', $validated) ? (int)$validated['state'] : 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $id = DB::table('list_wound_assessments')->insertGetId($payload);

            $this->logChange([
                'logType'    => 'Wound Assessment',
                'table'      => 'list_wound_assessments',
                'primaryKey' => (string)$id,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
            ]);

            $new = DB::table('list_wound_assessments')->where('id', $id)->first();

            DB::commit();

            // Para tu frontend (axios) devolvemos JSON
            return response()->json([
                'success' => true,
                'message' => 'Registro creado correctamente.',
                'data'    => $new,
            ], 201);

            // Si prefieres post-redirect con flashes (sin axios), usa:
            // return redirect()->route('wound_assessment.index')->with('success','Registro creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el registro.',
            ], 500);
        }
    }

    /**
     * Actualizar registro (PUT /wound_assessment/{id})
     */
    public function update(Request $request, int $id)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'type'        => ['required', 'string', 'max:100'],
                'name'        => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('list_wound_assessments')
                        ->ignore($id)->where(fn($q) => $q->where('type', $request->type))
                ],
                'description' => ['nullable', 'string'],
                'state'       => ['nullable', 'boolean'],
            ]);

            $old = DB::table('list_wound_assessments')->where('id', $id)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            $updates = [
                'type'        => $validated['type'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'state'       => array_key_exists('state', $validated) ? (int)$validated['state'] : $old->state,
                'updated_at'  => now(),
            ];

            // Log de cambios por campo
            foreach (['type', 'name', 'description', 'state'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'    => 'Wound Assessment',
                        'table'      => 'list_wound_assessments',
                        'primaryKey' => (string)$id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            DB::table('list_wound_assessments')->where('id', $id)->update($updates);
            $fresh = DB::table('list_wound_assessments')->where('id', $id)->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente.',
                'data'    => $fresh,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro.',
            ], 500);
        }
    }

    /**
     * Borrado lógico: state = 0 (DELETE /wound_assessment/{id})
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $old = DB::table('list_wound_assessments')->where('id', $id)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            DB::table('list_wound_assessments')->where('id', $id)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_wound_assessments')->where('id', $id)->first();

            $this->logChange([
                'logType'    => 'Wound Assessment',
                'table'      => 'list_wound_assessments',
                'primaryKey' => (string)$id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro.',
            ], 500);
        }
    }
}
