<?php

namespace App\Http\Controllers\Kurator;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\Patient;
use App\Models\Site;
use App\Models\State;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Str;

class KuratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kurators = DB::table('kurators')
            ->join('user_details', 'user_details.id', '=', 'kurators.user_detail_id')
            ->join('users', 'user_details.user_id', '=', 'users.id')
            ->join('list_sites', 'user_details.site_id', '=', 'list_sites.id')
            ->select(
                DB::raw("CONCAT(kurators.user_uuid, '-', user_details.name) as kurator_full_name"),
                'kurators.id as kurator_id',
                'kurators.user_uuid',
                'kurators.specialty',
                'kurators.type_kurator',
                'kurators.type_identification',
                'kurators.identification',
                'kurators.state',
                'user_details.name',
                'user_details.fatherLastName',
                'user_details.motherLastName',
                'user_details.sex',
                'user_details.site_id',
                'user_details.mobile',
                'user_details.contactEmail',
                'list_sites.siteName',
                'users.email'
            )
            ->get()
            ->map(function ($kurator) {
                $kurator->crypt_kurator = Crypt::encryptString($kurator->kurator_id);
                return $kurator;
            });

        $sites = DB::table('list_sites')->select('id', 'siteName')->get();

        return Inertia::render('Kurators/Index', [
            'kurators' => $kurators,
            'sites' => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Hombre,Mujer',
            'mobile' => 'nullable|string|max:20',
            'site_id' => 'required|exists:list_sites,id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'specialty' => 'required|string|max:255',
            'type_kurator' => 'required|string|max:50',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'sex' => $request->sex,
                'mobile' => $request->mobile,
                'contactEmail' => $request->email,
                'site_id' => $request->site_id,
            ]);

            $anio = Carbon::now()->format('Y');
            $site = str_pad($userDetail->site_id, 2, '0', STR_PAD_LEFT);
            $random = strtoupper(Str::random(3));

            Kurator::create([
                'user_uuid' => 'KU' . $anio . '-' . $site . $random,
                'user_detail_id' => $userDetail->id,
                'specialty' => $request->specialty,
                'type_kurator' => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Kurador creado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Crear kurador');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al crear el kurador.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $kurator = Kurator::findOrFail($id);
        $userDetail = $kurator->userDetail;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Hombre,Mujer',
            'mobile' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userDetail->user_id),
            ],
            'site_id' => 'required|exists:list_sites,id',
            'specialty' => 'required|string|max:255',
            'type_kurator' => 'required|string|max:50',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {

            DB::table('users')
                ->where('id', $userDetail->user_id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            DB::table('user_details')
                ->where('id', $userDetail->id)
                ->update([
                    'name' => $request->name,
                    'fatherLastName' => $request->fatherLastName,
                    'motherLastName' => $request->motherLastName,
                    'sex' => $request->sex,
                    'mobile' => $request->mobile,
                    'contactEmail' => $request->email,
                    'site_id' => $request->site_id,
                    'updated_at' => now(),
                ]);

            $kurator->update([
                'specialty' => $request->specialty,
                'type_kurator' => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Kurador actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Editar kurador');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al actualizar el kurador.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $kurator = Kurator::findOrFail($id);
            $userDetail = $kurator->userDetail;
            $userId = $userDetail->user_id;

            DB::beginTransaction();

            $kurator->delete();
            $userDetail->delete();
            DB::table('users')->where('id', $userId)->delete();

            DB::commit();

            // Respuesta JSON exitosa para peticiones ajax
            return response()->json([
                'message' => 'Kurador eliminado correctamente.'
            ], 200);
        } catch (\Throwable $e) {
            Log::info('Eliminar kurador');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'error' => 'Error al eliminar el kurador.'
            ], 500);
        }
    }

    public function byKurator($kuratorId)
    {
        try {
            $decryptkuratorId = Crypt::decryptString($kuratorId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $sites = Site::all();
        $states = State::all();

        $patientRecords = HealthRecord::with('patient.userDetail')
            ->get()
            ->map(function ($record) {
                $detail = $record->patient->userDetail;
                return [
                    'full_name' => "{$record->record_uuid} - {$detail->name} {$detail->fatherLastName}",
                    'health_record_id' => $record->id,
                ];
            });

        $kurators = Kurator::with('userDetail')
            ->get()
            ->map(function ($k) {
                $d = $k->userDetail;
                return [
                    'full_name' => "{$k->user_uuid} - {$d->name} {$d->fatherLastName}",
                    'kurator_id' => $k->id,
                ];
            });

        $appointmentsRaw = DB::table('patients')
            ->join('user_details as patient_user_details', 'patients.user_detail_id', '=', 'patient_user_details.id')
            ->leftJoin('health_records', 'health_records.patient_id', '=', 'patients.id')
            ->leftJoin('appointments', 'appointments.health_record_id', '=', 'health_records.id')
            ->leftJoin('list_sites', 'appointments.site_id', '=', 'list_sites.id')
            ->leftJoin('kurators', 'appointments.kurator_id', '=', 'kurators.id')
            ->leftJoin('user_details as kurator_user_details', 'kurators.user_detail_id', '=', 'kurator_user_details.id')
            ->where('appointments.kurator_id', $decryptkuratorId)
            ->select(
                'patients.id as patient_id',
                DB::raw("CONCAT(
            COALESCE(patients.user_uuid, ''), ' - ',
            COALESCE(patient_user_details.name, ''), ' ',
            COALESCE(patient_user_details.fatherLastName, ''), ' ',
            COALESCE(patient_user_details.motherLastName, '')
        ) as patient_full_name"),
                DB::raw("COALESCE(health_records.record_uuid, 'Sin expediente') as health_record_uuid"),
                'health_records.id as health_record_id',
                'appointments.id as appointment_id',
                'appointments.dateStartVisit',
                'appointments.typeVisit',
                'list_sites.siteName as site_name',
                DB::raw("CONCAT(kurators.user_uuid, '-', COALESCE(kurator_user_details.name, '')) as kurator_full_name")
            )
            ->orderBy('patients.id')
            ->orderBy('appointments.dateStartVisit', 'desc')
            ->get();

        $appointments = $appointmentsRaw
            ->groupBy('patient_id')
            ->map(function ($items) {
                $first = $items->first();

                $firstFolio = $items->pluck('health_record_uuid')
                    ->filter(fn($folio) => $folio !== 'Sin expediente' && !is_null($folio))
                    ->first() ?? 'Sin expediente';

                return [
                    'patient_id' => $first->patient_id,
                    'patient_full_name' => $first->patient_full_name,
                    'health_record_uuid' => $firstFolio,
                    'appointments' => $items->filter(fn($r) => $r->appointment_id !== null)->map(fn($app) => [
                        'crypt_appointment_id' => Crypt::encryptString($app->appointment_id),
                        'dateStartVisit' => $app->dateStartVisit,
                        'typeVisit' => $app->typeVisit,
                        'site_name' => $app->site_name,
                        'kurator_full_name' => $app->kurator_full_name,
                        'health_record_uuid' => $app->health_record_uuid,
                        'crypt_health_record_id' => Crypt::encryptString($app->health_record_id),
                    ])->values(),
                ];
            })->values();

        return Inertia::render('Kurators/Appointments', [
            'states' => $states,
            'sites' => $sites,
            'patientRecords' => $patientRecords,
            'kurators' => $kurators,
            'appointments' => $appointments,
        ]);
    }
}
