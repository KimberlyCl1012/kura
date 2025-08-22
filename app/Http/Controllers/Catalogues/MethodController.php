<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MethodController extends Controller
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
            ->select(
                'list_treatment_methods.id',
                'list_treatment_methods.name',
                'list_treatment_methods.description',
            )
            ->where('list_treatment_methods.state', 1)
            ->get()
            ->map(function ($methods) {
                $methods->id = Crypt::encryptString($methods->id);
                return $methods;
            });

        return Inertia::render('Catalogues/MethodTreatments/Index', ([
            'methods'  =>  $methods
        ]));
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

            $method = Method::create($payload);

            $this->logChange([
                'logType'    => 'Metodos del tratamiento',
                'table'      => 'list_treatment_methods',
                'primaryKey' => (string)$method->id,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metodo creado exitosamente',
                'data' => [
                    ...$method->toArray(),
                    'id' => Crypt::encryptString($method->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear metodo del tratamiento');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar el metodo del tratamiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $encryptedId)
    {
        DB::beginTransaction();

        try {
            $id = Crypt::decryptString($encryptedId);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $method = Method::findOrFail($id);

            $old = $method->only(['name', 'description']);

            $updates = [
                'name'        => $request->name,
                'description' => $request->description,
            ];

            foreach ($updates as $campo => $nuevo) {
                $viejo = $old[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'    => 'Metodo del tratamiento',
                        'table'      => 'list_treatment_methods',
                        'primaryKey' => (string)$id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            $method->update($updates);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metodo del tratamiento actualizado correctamente',
                'data' => [
                    ...$method->toArray(),
                    'id' => Crypt::encryptString($method->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Editar metodo del tratamiento');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($encryptedId)
    {
        DB::beginTransaction();

        try {
            $id = Crypt::decryptString($encryptedId);

            $method = Method::findOrFail($id);

            $old = $method->toArray();

            $method->update(['state' => 0]);

            $new = $method->fresh()->toArray();

            $this->logChange([
                'logType'    => 'Metodo del tratamiento',
                'table'      => 'list_treatment_methods',
                'primaryKey' => (string)$id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metodos del tratamiento eliminado correctamente',
                'id' => $encryptedId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Eliminar metodo del tratamiento');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submethods($methodId)
    {
        $method = Method::with(['submethods' => function ($query) {
            $query->where('state', 1)
                ->select('id', 'method_id', 'name');
        }])->findOrFail($methodId);

        return response()->json($method->submethods);
    }
}
