<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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

        $site = DB::table('list_sites')->insertGetId([
            'address_id' => $request->address_id,
            'siteName' => $request->siteName,
            'email_admin' => $request->email_admin,
            'phone' => $request->phone,
            'description' => $request->description,
            'state' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $siteData = DB::table('list_sites')->where('id', $site)->first();
        $siteData->id = Crypt::encryptString($siteData->id);

        return response()->json([
            'success' => true,
            'message' => 'Registro creado exitosamente',
            'data' => $siteData,
        ]);
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
                'message' => 'Registro actualizado correctamente',
                'data' => $siteData,
            ]);
        } catch (\Exception $e) {
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
                'message' => 'Registro eliminado correctamente',
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
