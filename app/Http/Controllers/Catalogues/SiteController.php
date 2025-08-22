<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SiteController extends Controller
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
        $addresses = Address::all()->map(function ($address) {
            $address->name = "{$address->streetAddress} {$address->addressLine2}, {$address->city}, {$address->postalCode}";
            return $address;
        });

        $sites = DB::table('list_sites')
            ->join('list_addresses', 'list_sites.address_id', 'list_addresses.id')
            ->select(
                'list_sites.id',
                'list_sites.address_id',
                'list_sites.siteName',
                'list_sites.email_admin',
                'list_sites.phone',
                'list_sites.description',
                'list_addresses.streetAddress'
            )
            ->where('list_sites.state', 1)
            ->get()
            ->map(function ($site) {
                $site->id = Crypt::encryptString($site->id);
                return $site;
            });

        return Inertia::render('Catalogues/Sites/Index', [
            'sites' => $sites,
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id'   => 'required|exists:list_addresses,id',
            'siteName'     => 'required|string|max:255',
            'email_admin'  => 'required|email|max:255',
            'phone'        => 'required|string|max:50',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $payload = [
                'address_id'  => $request->address_id,
                'siteName'    => $request->siteName,
                'email_admin' => $request->email_admin,
                'phone'       => $request->phone,
                'description' => $request->description,
                'state'       => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            $siteId = DB::table('list_sites')->insertGetId($payload);

            $this->logChange([
                'logType'      => 'Sitio',
                'table'        => 'list_sites',
                'primaryKey'   => (string)$siteId,
                'secondaryKey' => (string)$request->address_id,
                'changeType'   => 'create',
                'newValue'     => json_encode($payload),
            ]);

            $siteData = DB::table('list_sites')->where('id', $siteId)->first();
            DB::commit();

            $siteData->id = Crypt::encryptString($siteData->id);

            return response()->json([
                'success' => true,
                'message' => 'Sitio creado exitosamente',
                'data'    => $siteData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear Sitio');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar el sitio.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $decryptedId = Crypt::decryptString($id);

            $validator = Validator::make($request->all(), [
                'address_id'   => 'required|exists:list_addresses,id',
                'siteName'     => 'required|string|max:255',
                'email_admin'  => 'required|email|max:255',
                'phone'        => 'required|string|max:50',
                'description'  => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $old = DB::table('list_sites')->where('id', $decryptedId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Sitio no encontrado'], 404);
            }

            $updates = [
                'address_id'  => $request->address_id,
                'siteName'    => $request->siteName,
                'email_admin' => $request->email_admin,
                'phone'       => $request->phone,
                'description' => $request->description,
                'updated_at'  => now(),
            ];

            foreach (['address_id', 'siteName', 'email_admin', 'phone', 'description'] as $campo) {
                $viejo = $old->$campo;
                $nuevo = $updates[$campo];
                if ((string)$viejo !== (string)$nuevo) {
                    $this->logChange([
                        'logType'      => 'Sitio',
                        'table'        => 'list_sites',
                        'primaryKey'   => (string)$decryptedId,
                        'secondaryKey' => (string)$updates['address_id'],
                        'changeType'   => 'update',
                        'fieldName'    => $campo,
                        'oldValue'     => $viejo,
                        'newValue'     => $nuevo,
                    ]);
                }
            }

            DB::table('list_sites')->where('id', $decryptedId)->update($updates);

            $siteData = DB::table('list_sites')->where('id', $decryptedId)->first();
            DB::commit();

            $siteData->id = Crypt::encryptString($siteData->id);

            return response()->json([
                'success' => true,
                'message' => 'Sitio actualizado correctamente',
                'data'    => $siteData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Editar Sitio');
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
        DB::beginTransaction();

        try {
            $decryptedId = Crypt::decryptString($id);

            $old = DB::table('list_sites')->where('id', $decryptedId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Sitio no encontrado'], 404);
            }

            DB::table('list_sites')->where('id', $decryptedId)->update([
                'state'      => 0,
                'updated_at' => now(),
            ]);

            $new = DB::table('list_sites')->where('id', $decryptedId)->first();

            $this->logChange([
                'logType'      => 'Sitio',
                'table'        => 'list_sites',
                'primaryKey'   => (string)$decryptedId,
                'secondaryKey' => (string)$old->address_id,
                'changeType'   => 'destroy',
                'oldValue'     => json_encode($old),
                'newValue'     => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sitio eliminado correctamente',
                'id'      => $id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Eliminar Sitio');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
