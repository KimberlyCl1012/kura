<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Address;
use App\Models\Site;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AddressController extends Controller
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'streetAddress' => 'required|string|max:255',
            'postalCode'    => 'required|string|max:10',
            'state_id'      => 'required|exists:list_states,id',
        ]);

        DB::beginTransaction();

        try {
            $payload = [
                'type'           => 'Local',
                'streetAddress'  => $request->streetAddress,
                'addressLine2'   => $request->addressLine2,
                'postalCode'     => $request->postalCode,
                'city'           => $request->city ?? 'México',
                'state_id'       => $request->state_id,
                'country'        => $request->country ?? 'MX',
                'state'          => 1,
            ];

            $addressId = DB::table('list_addresses')->insertGetId($payload);

            $this->logChange([
                'logType'    => 'Dirección',
                'table'      => 'list_addresses',
                'primaryKey' => (string) $addressId,
                'changeType' => 'create',
                'newValue'   => json_encode($payload),
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

            DB::commit();

            $address->address_id = Crypt::encryptString($address->address_id);

            return response()->json([
                'success' => true,
                'message' => 'Dirección creada correctamente',
                'data'    => $address,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la dirección',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'streetAddress' => 'required|string|max:255',
            'postalCode'    => 'required|string|max:10',
            'state_id'      => 'required|exists:list_states,id',
        ]);

        DB::beginTransaction();

        try {
            $addressId = Crypt::decryptString($id);

            $old = DB::table('list_addresses')->where('id', $addressId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            $updates = [
                'streetAddress' => $request->streetAddress,
                'addressLine2'  => $request->addressLine2,
                'postalCode'    => $request->postalCode,
                'city'          => $request->city ?? 'México',
                'state_id'      => $request->state_id,
                'country'       => $request->country ?? 'MX',
            ];

            foreach ($updates as $campo => $nuevo) {
                $viejo = $old->$campo;
                if ((string) $viejo !== (string) $nuevo) {
                    $this->logChange([
                        'logType'    => 'Dirección',
                        'table'      => 'list_addresses',
                        'primaryKey' => (string) $addressId,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $viejo,
                        'newValue'   => $nuevo,
                    ]);
                }
            }

            DB::table('list_addresses')->where('id', $addressId)->update($updates);

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

            DB::commit();

            $address->address_id = Crypt::encryptString($address->address_id);

            return response()->json([
                'success' => true,
                'message' => 'Dirección actualizada correctamente',
                'data'    => $address,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la dirección',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $addressId = Crypt::decryptString($id);

            $old = DB::table('list_addresses')->where('id', $addressId)->first();
            if (!$old) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            DB::table('list_addresses')->where('id', $addressId)->update(['state' => 0]);

            $new = DB::table('list_addresses')->where('id', $addressId)->first();

            $this->logChange([
                'logType'    => 'Dirección',
                'table'      => 'list_addresses',
                'primaryKey' => (string) $addressId,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($old),
                'newValue'   => json_encode($new),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dirección eliminada correctamente',
                'id'      => $addressId,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
