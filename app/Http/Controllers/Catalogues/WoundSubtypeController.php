<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\WoundSubtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WoundSubtypeController extends Controller
{
    public function index()
    {
        $woundsTypes = DB::table('list_wound_types')
            ->where('state', 1)
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                return $item;
            });

        $woundsSubtypes = DB::table('list_wound_subtypes')
            ->where('state', 1)
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
        try {
            $realWoundTypeId = Crypt::decryptString($request->wound_type_id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $id = DB::table('list_wound_subtypes')->insertGetId([
                'wound_type_id' => $realWoundTypeId,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'state' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subtype = DB::table('list_wound_subtypes')->where('id', $id)->first();
            $subtype->id = Crypt::encryptString($subtype->id);
            $subtype->wound_type_id = $request->wound_type_id;

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida creado correctamente.',
                'data' => $subtype,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el subtipo.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $realId = Crypt::decryptString($id);
            $realWoundTypeId = Crypt::decryptString($request->wound_type_id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::table('list_wound_subtypes')->where('id', $realId)->update([
                'wound_type_id' => $realWoundTypeId,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'updated_at' => now(),
            ]);

            $subtype = DB::table('list_wound_subtypes')->where('id', $realId)->first();
            $subtype->id = Crypt::encryptString($subtype->id);
            $subtype->wound_type_id = $request->wound_type_id;

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida actualizado correctamente.',
                'data' => $subtype,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el subtipo.',
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $realId = Crypt::decryptString($id);

            DB::table('list_wound_subtypes')->where('id', $realId)->update([
                'state' => 0,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subtipo de herida eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el subtipo.',
            ], 500);
        }
    }
}
