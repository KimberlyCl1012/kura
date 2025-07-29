<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\BodyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BodyLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        try {
            $location = BodyLocation::create($request->only('name', 'description'));

            return response()->json([
                'success' => true,
                'message' => 'Ubicación creada exitosamente',
                'data' => [
                    ...$location->toArray(),
                    'id' => Crypt::encryptString($location->id),
                ],
            ]);
        } catch (\Exception $e) {
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

    // Actualizar registro
    public function update(Request $request, $encryptedId)
    {
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
            $location->update($request->only('name', 'description'));

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada correctamente',
                'data' => [
                    ...$location->toArray(),
                    'id' => Crypt::encryptString($location->id),
                ],
            ]);
        } catch (\Exception $e) {
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
        try {
            $id = Crypt::decryptString($encryptedId);

            $location = BodyLocation::findOrFail($id);
            $location->update(['state' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación eliminada correctamente',
                'id' => $encryptedId,
            ]);
        } catch (\Exception $e) {
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
