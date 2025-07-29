<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\WoundType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $id = DB::table('list_wound_types')->insertGetId([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'state' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $woundType = DB::table('list_wound_types')->where('id', $id)->first();
            $woundType->id = Crypt::encryptString($woundType->id);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida creado correctamente.',
                'data' => $woundType,
            ]);
        } catch (\Exception $e) {
            Log::info('Eliminar tipo de herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el tipo de herida.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $realId = Crypt::decryptString($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::table('list_wound_types')->where('id', $realId)->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'updated_at' => now(),
            ]);

            $woundType = DB::table('list_wound_types')->where('id', $realId)->first();
            $woundType->id = Crypt::encryptString($woundType->id);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida actualizado correctamente.',
                'data' => $woundType,
            ]);
        } catch (\Exception $e) {
            Log::info('Eliminar tipo de herida');
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
        try {
            $realId = Crypt::decryptString($id);

            DB::table('list_wound_types')->where('id', $realId)->update([
                'state' => 0,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de herida eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
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
