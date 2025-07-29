<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'address_id' => 'required|exists:list_addresses,id',
            'siteName' => 'required|string|max:255',
            'email_admin' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $siteId = DB::table('list_sites')->insertGetId([
                'address_id' => $request->address_id,
                'siteName' => $request->siteName,
                'email_admin' => $request->email_admin,
                'phone' => $request->phone,
                'description' => $request->description,
                'state' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $siteData = DB::table('list_sites')->where('id', $siteId)->first();
            $siteData->id = Crypt::encryptString($siteData->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sitio creado exitosamente',
                'data' => $siteData,
            ]);
        } catch (\Exception $e) {
            Log::info('Crear Sitio');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar el sitio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);

            $validator = Validator::make($request->all(), [
                'address_id' => 'required|exists:list_addresses,id',
                'siteName' => 'required|string|max:255',
                'email_admin' => 'required|email|max:255',
                'phone' => 'required|string|max:50',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            DB::table('list_sites')->where('id', $decryptedId)->update([
                'address_id' => $request->address_id,
                'siteName' => $request->siteName,
                'email_admin' => $request->email_admin,
                'phone' => $request->phone,
                'description' => $request->description,
                'updated_at' => now(),
            ]);

            $siteData = DB::table('list_sites')->where('id', $decryptedId)->first();
            $siteData->id = Crypt::encryptString($siteData->id);

            return response()->json([
                'success' => true,
                'message' => 'Sitio actualizado correctamente',
                'data' => $siteData,
            ]);
        } catch (\Exception $e) {
            Log::info('Editar Sitio');
            Log::debug($e);
            DB::rollBack();
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
            $decryptedId = Crypt::decryptString($id);

            DB::table('list_sites')->where('id', $decryptedId)->update([
                'state' => 0,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sitio eliminado correctamente',
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            Log::info('Eliminar Sitio');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
