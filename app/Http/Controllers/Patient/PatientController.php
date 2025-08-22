<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Str;

class PatientController extends Controller
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
        $patients = DB::table('patients')
            ->join('user_details', 'user_details.id', '=', 'patients.user_detail_id')
            ->join('users', 'user_details.user_id', '=', 'users.id')
            ->join('list_sites', 'user_details.site_id', '=', 'list_sites.id')
            ->leftJoin('health_records', 'health_records.patient_id', '=', 'patients.id')
            ->select(
                'patients.id as patient_id',
                'patients.user_uuid',
                'patients.dateOfBirth',
                'patients.identification',
                'patients.state',
                'user_details.name',
                'user_details.fatherLastName',
                'user_details.motherLastName',
                'user_details.sex',
                'users.id as user_id',
                'users.email',
                'list_sites.siteName',
                'health_records.id as health_record_id',
                'patients.type_identification',
                'patients.type_identification_kinship',
                'patients.identification_kinship',
                'patients.streetAddress',
                'patients.city',
                'patients.postalCode',
                'patients.relativeName',
                'patients.kinship',
                'patients.relativeMobile',
                'patients.state_id',
                'user_details.site_id',
                'patients.consent'
            )
            // 🔹 Filtra por activos
            ->where('patients.state', 1)
            ->where('user_details.state', 1)
            ->where('users.state', 1)
            ->get()
            ->map(function ($patient) {
                $patient->crypt_patient = Crypt::encryptString($patient->patient_id);
                return $patient;
            });

        $states = DB::table('list_states')->select('id', 'name')->get();
        $sites = DB::table('list_sites')->select('id', 'siteName')->get();

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'states' => $states,
            'sites' => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'sexo' => 'nullable|string|in:Hombre,Mujer',
            'site_id' => 'required|exists:list_sites,id',
            'state_id' => 'required|exists:list_states,id',
            'dateOfBirth' => 'required|date',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
            'streetAddress' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:12',
            'relativeName' => 'nullable|string|max:255',
            'kinship' => 'nullable|string|max:50',
            'relativeMobile' => 'nullable|string|max:20',
            'consent' => 'required|boolean|in:1',
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt('kura+123'),
            ]);

            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'users',
                'primaryKey' => $user->id,
                'changeType' => 'create',
                'newValue'   => json_encode([
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            ]);

            // 2. Crear detalles
            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'contactEmail' => $request->email,
                'mobile' => $request->mobile,
                'sex' => $request->sex,
                'site_id' => $request->site_id,
                'company_id' => 1,
            ]);

            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'user_details',
                'primaryKey' => $userDetail->id,
                'secondaryKey' => $user->id,
                'changeType' => 'create',
                'newValue'   => json_encode($userDetail->only([
                    'name',
                    'fatherLastName',
                    'motherLastName',
                    'sex',
                    'mobile',
                    'contactEmail',
                    'site_id'
                ])),
            ]);

            // 3. Crear paciente
            $anio = Carbon::now()->format('Y');
            $site = str_pad($userDetail->site_id, 2, '0', STR_PAD_LEFT);
            $random = Str::upper(Str::random(2));

            $patient = Patient::create([
                'user_detail_id' => $userDetail->id,
                'user_uuid' => 'PA' . $anio . '-' . $site . $random,
                'state_id' => $request->state_id,
                'dateOfBirth' => Carbon::parse($request->dateOfBirth)->format('Y-m-d'),
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
                'streetAddress' => $request->streetAddress,
                'city' => $request->city,
                'postalCode' => $request->postalCode,
                'relativeName' => $request->relativeName,
                'kinship' => $request->kinship,
                'type_identification_kinship' => $request->type_identification_kinship,
                'identification_kinship' => $request->identification_kinship,
                'relativeMobile' => $request->relativeMobile,
                'consent' => $request->has('consent') ? (bool) $request->consent : false,
            ]);

            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'patients',
                'primaryKey' => $patient->id,
                'secondaryKey' => $userDetail->id,
                'changeType' => 'create',
                'newValue'   => json_encode($patient->only([
                    'user_uuid',
                    'state_id',
                    'dateOfBirth',
                    'type_identification',
                    'identification',
                    'streetAddress',
                    'city',
                    'postalCode',
                    'relativeName',
                    'kinship',
                    'type_identification_kinship',
                    'identification_kinship',
                    'relativeMobile',
                    'consent'
                ])),
            ]);

            DB::commit();
            return redirect()->route('patients.index')->with('success', 'Paciente creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al crear el paciente.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $patientId = is_numeric($id) ? $id : Crypt::decryptString($id);
        $patient = Patient::findOrFail($patientId);
        $userDetail = $patient->userDetail;
        $user = User::find($userDetail->user_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $patient->userDetail->user_id,
            'mobile' => 'nullable|string|max:20',
            'sex' => 'required|string|in:Hombre,Mujer',
            'site_id' => 'required|exists:list_sites,id',
            'state_id' => 'required|exists:list_states,id',
            'dateOfBirth' => 'required|date',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
            'streetAddress' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:12',
            'relativeName' => 'nullable|string|max:255',
            'kinship' => 'nullable|string|max:50',
            'relativeMobile' => 'nullable|string|max:20',
        ]);


        DB::beginTransaction();

        try {
            // USERS
            foreach (['name', 'email'] as $campo) {
                if ((string)$user->$campo !== (string)$request->$campo) {
                    $this->logChange([
                        'logType'    => 'Paciente',
                        'table'      => 'users',
                        'primaryKey' => $user->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $user->$campo,
                        'newValue'   => $request->$campo,
                    ]);
                }
            }
            $user->update(['name' => $request->name, 'email' => $request->email]);

            // USER_DETAILS
            $camposDetail = ['name', 'fatherLastName', 'motherLastName', 'sex', 'mobile', 'contactEmail', 'site_id'];
            foreach ($camposDetail as $campo) {
                $valorNuevo = $campo === 'contactEmail' ? $request->email : $request->$campo;
                if ((string)$userDetail->$campo !== (string)$valorNuevo) {
                    $this->logChange([
                        'logType'    => 'Paciente',
                        'table'      => 'user_details',
                        'primaryKey' => $userDetail->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $userDetail->$campo,
                        'newValue'   => $valorNuevo,
                    ]);
                }
            }
            $userDetail->update([
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'contactEmail' => $request->email,
                'mobile' => $request->mobile,
                'sex' => $request->sex,
                'site_id' => $request->site_id,
            ]);

            // PATIENTS
            $camposPatient = [
                'state_id',
                'dateOfBirth',
                'type_identification',
                'identification',
                'streetAddress',
                'city',
                'postalCode',
                'relativeName',
                'kinship',
                'type_identification_kinship',
                'identification_kinship',
                'relativeMobile'
            ];
            foreach ($camposPatient as $campo) {
                $valorNuevo = $campo === 'dateOfBirth'
                    ? Carbon::parse($request->$campo)->format('Y-m-d')
                    : $request->$campo;
                if ((string)$patient->$campo !== (string)$valorNuevo) {
                    $this->logChange([
                        'logType'    => 'Paciente',
                        'table'      => 'patients',
                        'primaryKey' => $patient->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $patient->$campo,
                        'newValue'   => $valorNuevo,
                    ]);
                }
            }
            $patient->update([
                'state_id' => $request->state_id,
                'dateOfBirth' => Carbon::parse($request->dateOfBirth)->format('Y-m-d'),
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
                'streetAddress' => $request->streetAddress,
                'city' => $request->city,
                'postalCode' => $request->postalCode,
                'relativeName' => $request->relativeName,
                'kinship' => $request->kinship,
                'type_identification_kinship' => $request->type_identification_kinship,
                'identification_kinship' => $request->identification_kinship,
                'relativeMobile' => $request->relativeMobile,
            ]);

            DB::commit();
            return redirect()->route('patients.index')->with('success', 'Paciente actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al actualizar el paciente.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {

            $openAppointment = DB::table('appointments')
                ->join('health_records', 'appointments.health_record_id', '=', 'health_records.id')
                ->where('health_records.patient_id', $id)
                ->whereIn('appointments.state', [1, 2])
                ->exists();

            if ($openAppointment) {
                return response()->json([
                    'message' => 'No se puede eliminar el paciente: existe una consulta en curso.'
                ], 422);
            }

            $patient = Patient::findOrFail($id);
            $userDetail = $patient->userDetail;
            $userId = $userDetail->user_id;

            DB::beginTransaction();

            $oldPatient = $patient->toArray();
            $oldUserDetail = $userDetail->toArray();
            $user = User::findOrFail($userId);

            $patient->update(['state' => 0]);
            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'patients',
                'primaryKey' => $patient->id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($oldPatient),
                'newValue'   => json_encode($patient->toArray()),
            ]);

            $userDetail->update(['state' => 0]);
            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'user_details',
                'primaryKey' => $userDetail->id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($oldUserDetail),
                'newValue'   => json_encode($userDetail->toArray()),
            ]);

            $user->state = 0;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Paciente desactivado correctamente.',
                'deleted_patient_id' => $patient->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['message' => 'Error al desactivar el paciente.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $decryptpatientId = Crypt::decryptString($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $patient = Patient::with('userDetail')->findOrFail($decryptpatientId);

        return response()->json([
            'name' => $patient->userDetail->name,
            'fatherLastName' => $patient->userDetail->fatherLastName,
            'motherLastName' => $patient->userDetail->motherLastName,
            'sex' => $patient->userDetail->sex,
            'site_id' => $patient->userDetail->site_id,
            'mobile' => $patient->userDetail->mobile,
            'email' => $patient->userDetail->contactEmail,
            'dateOfBirth' => $patient->dateOfBirth,
            'state_id' => $patient->state_id,
            'streetAddress' => $patient->streetAddress,
            'postalCode' => $patient->postalCode,
            'relativeName' => $patient->relativeName,
            'kinship' => $patient->kinship,
            'relativeMobile' => $patient->relativeMobile,
            'type_identification' => $patient->type_identification,
            'identification' => $patient->identification,
        ]);
    }
}
