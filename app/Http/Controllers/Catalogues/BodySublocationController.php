<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\BodySublocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BodySublocationController extends Controller
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
            ->where('state', 1)
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                return $item;
            });

        $bodySublocations = DB::table('list_body_sublocations as sub')
            ->join('list_body_locations as loc', 'loc.id', '=', 'sub.body_location_id')
            ->where('sub.state', 1)
            ->select(
                'sub.*',
                'loc.name as body_location_name'
            )
            ->get()
            ->map(function ($item) {
                $item->id = Crypt::encryptString($item->id);
                $item->body_location_id = Crypt::encryptString($item->body_location_id);
                return $item;
            });

        return Inertia::render('Catalogues/BodySublocations/Index', [
            'bodyLocations' => $bodyLocations,
            'bodySublocations' => $bodySublocations,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $realBodyLocationId = Crypt::decryptString($request->body_location_id);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $payload = [
                'body_location_id' => $realBodyLocationId,
                'name'             => $validated['name'],
                'description'      => $validated['description'] ?? null,
                'state'            => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $id = DB::table('list_body_sublocations')->insertGetId($payload);

            $this->logChange([
                'logType'      => 'Ubicación corporal secundaria',
                'table'        => 'list_body_sublocations',
                'primaryKey'   => (string)$id,
                'secondaryKey' => (string)$realBodyLocationId,
                'changeType'   => 'create',
                'newValue'     => json_encode($payload),
            ]);

            $sublocation = DB::table('list_body_sublocations')->where('id', $id)->first();
            DB::commit();

            $sublocation->id = Crypt::encryptString($sublocation->id);
            $sublocation->body_location_id = $request->body_location_id;

            return response()->json([
                'success' => true,
                'message' => 'Ubicación corporal secundaria creada correctamente.',
                'data'    => $sublocation,
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

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);
            $realBodyLocationId = Crypt::decryptString($request->body_location_id);

            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $old = DB::table('list_body_sublocations')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            $updates = [
                'body_location_id' => $realBodyLocationId,
                'name'             => $validated['name'],
                'description'      => $validated['description'] ?? null,
                'updated_at'       => now(),
            ];

            foreach (['body_location_id', 'name', 'description'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'      => 'Ubicación corporal secundaria',
                        'table'        => 'list_body_sublocations',
                        'primaryKey'   => (string)$realId,
                        'secondaryKey' => (string)$realBodyLocationId,
                        'changeType'   => 'update',
                        'fieldName'    => $campo,
                        'oldValue'     => $viejo,
                        'newValue'     => $nuevo,
                    ]);
                }
            }

            DB::table('list_body_sublocations')->where('id', $realId)->update($updates);

            $subtype = DB::table('list_body_sublocations')->where('id', $realId)->first();
            DB::commit();

            $subtype->id = Crypt::encryptString($subtype->id);
            $subtype->body_location_id = $request->body_location_id;

            return response()->json([
                'success' => true,
                'message' => 'Ubicación corporal secundaria actualizada correctamente.',
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

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $realId = Crypt::decryptString($id);

            $old = DB::table('list_body_sublocations')->where('id', $realId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }

            DB::table('list_body_sublocations')->where('id', $realId)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_body_sublocations')->where('id', $realId)->first();

            $this->logChange([
                'logType'      => 'Ubicación corporal secundaria',
                'table'        => 'list_body_sublocations',
                'primaryKey'   => (string)$realId,
                'secondaryKey' => (string)$old->body_location_id,
                'changeType'   => 'destroy',
                'oldValue'     => json_encode($old),
                'newValue'     => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación corporal secundaria eliminada correctamente.',
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
