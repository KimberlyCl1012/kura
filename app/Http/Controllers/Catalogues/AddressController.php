<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Catalogs
        $states = State::all();

        $addresses = DB::table('list_addresses')
            ->join('list_states', 'list_addresses.state_id', 'list_states.id')
            ->select(
                'list_addresses.id as address_id',
                'list_addresses.streetAddress',
                'list_addresses.addressLine2',
                'list_addresses.city',
                'list_addresses.state',
                'list_addresses.postalCode',
                'list_states.name as state'
            )
            ->where('list_addresses.state', 1)
            ->get()
            ->map(function ($addresses) {
                $addresses->address_id = Crypt::encryptString($addresses->address_id);
                return $addresses;
            });

        return Inertia::render('Catalogues/Addresses/Index', ([
            'addresses'  =>  $addresses,
            'states'  =>  $states,
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Ejemplo store()
    public function store(Request $request)
    {
        $validated = $request->validate([
            'streetAddress' => 'required|string|max:255',
            'postalCode'    => 'required|string|max:10',
            'state_id'      => 'required|exists:list_states,id',
        ]);

        try {
            $addressId = DB::table('list_addresses')->insertGetId([
                'type'           => 'Local',
                'streetAddress'  => $request->streetAddress,
                'addressLine2'   => $request->addressLine2,
                'postalCode'     => $request->postalCode,
                'city'           => $request->city ?? 'México',
                'state_id'       => $request->state_id,
                'country'        => $request->country ?? 'MX',
                'state'          => 1,
            ]);

            $address = DB::table('list_addresses')
                ->join('list_states', 'list_addresses.state_id', '=', 'list_states.id')
                ->select(
                    'list_addresses.id as address_id',
                    'list_addresses.streetAddress',
                    'list_addresses.addressLine2',
                    'list_addresses.city',
                    'list_addresses.postalCode',
                    'list_addresses.state_id',
                    'list_states.name as state',
                    'list_addresses.country'
                )
                ->where('list_addresses.id', $addressId)
                ->first();


            $address->address_id = Crypt::encryptString($address->address_id);

            return response()->json([
                'success' => true,
                'message' => 'Dirección creada correctamente',
                'data'    => $address,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la dirección',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'streetAddress' => 'required|string|max:255',
            'postalCode'    => 'required|string|max:10',
            'state_id'      => 'required|exists:list_states,id',
        ]);

        try {
            $addressId = Crypt::decryptString($id); // Desencripta el ID recibido

            DB::table('list_addresses')
                ->where('id', $addressId)
                ->update([
                    'streetAddress'  => $request->streetAddress,
                    'addressLine2'   => $request->addressLine2,
                    'postalCode'     => $request->postalCode,
                    'city'           => $request->city ?? 'México',
                    'state_id'       => $request->state_id,
                    'country'        => $request->country ?? 'MX',
                ]);

            // Recuperar registro actualizado
            $address = DB::table('list_addresses')
                ->join('list_states', 'list_addresses.state_id', '=', 'list_states.id')
                ->select(
                    'list_addresses.id as address_id',
                    'list_addresses.streetAddress',
                    'list_addresses.addressLine2',
                    'list_addresses.city',
                    'list_addresses.postalCode',
                    'list_addresses.state_id',
                    'list_states.name as state',
                    'list_addresses.country'
                )
                ->where('list_addresses.id', $addressId)
                ->first();


            $address->address_id = Crypt::encryptString($address->address_id);

            return response()->json([
                'success' => true,
                'message' => 'Dirección actualizada correctamente',
                'data'    => $address,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la dirección',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decryptString($id);

            DB::table('list_addresses')
                ->where('id', $id)
                ->update(['state' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Dirección eliminada correctamente',
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
