<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\BodyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BodyLocationController extends Controller
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
        $bodyLocations = DB::table('list_body_locations')
            ->select(
                'list_body_locations.id',
                'list_body_locations.name',
                'list_body_locations.description',
            )
            ->where('list_body_locations.state', 1)
            ->get()
            ->map(function ($bodyLocations) {
                $bodyLocations->id = Crypt::encryptString($bodyLocations->id);
                return $bodyLocations;
            });

        return Inertia::render('Catalogues/BodyLocations/Index', ([
            'bodyLocations'  =>  $bodyLocations
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

            $location = BodyLocation::create($payload);

            $this->logChange([
                'logType'    => 'Ubicación corporal',
                'table'      => 'list_body_locations',
                'primaryKey' => (string)$location->id,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación creada exitosamente',
                'data' => [
                    ...$location->toArray(),
                    'id' => Crypt::encryptString($location->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear ubicación corporal');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la localización.',
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

            $location = BodyLocation::findOrFail($id);

            $old = $location->only(['name', 'description']);

            $updates = [
                'name'        => $request->name,
                'description' => $request->description,
            ];

            foreach ($updates as $campo => $nuevo) {
                $viejo = $old[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'    => 'Ubicación corporal',
                        'table'      => 'list_body_locations',
                        'primaryKey' => (string)$id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            $location->update($updates);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada correctamente',
                'data' => [
                    ...$location->toArray(),
                    'id' => Crypt::encryptString($location->id),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Editar ubicación corporal');
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

            $location = BodyLocation::findOrFail($id);

            $old = $location->toArray();

            $location->update(['state' => 0]);

            $new = $location->fresh()->toArray();

            $this->logChange([
                'logType'    => 'Ubicación corporal',
                'table'      => 'list_body_locations',
                'primaryKey' => (string)$id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación eliminada correctamente',
                'id' => $encryptedId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Eliminar ubicación corporal');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sublocations($bodyLocationId)
    {
        $location = BodyLocation::with(['bodySublocations' => function ($query) {
            $query->where('state', 1)
                ->select('id', 'body_location_id', 'name');
        }])->findOrFail($bodyLocationId);

        return response()->json($location->bodySublocations);
    }
}
