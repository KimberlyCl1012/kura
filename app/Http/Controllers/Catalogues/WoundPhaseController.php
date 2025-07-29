<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\WoundPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundPhaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $phase = WoundPhase::create($request->only('name', 'description'));

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
            Log::info('Eliminar fase de la herida');
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

        try {
            $id = Crypt::decryptString($id);

            $phase = WoundPhase::findOrFail($id);
            $phase->update($request->only('name', 'description'));

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
            Log::info('Eliminar fase de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $phase = WoundPhase::findOrFail($id);
            $phase->update(['state' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Fase eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::info('Eliminar fase de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
