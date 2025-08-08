<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
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
            // tus validaciones...
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
            // tus validaciones...
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
            $patient = Patient::findOrFail($id);
            $userDetail = $patient->userDetail;
            $userId = $userDetail->user_id;

            DB::beginTransaction();

            $patient->delete();
            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'patients',
                'primaryKey' => $patient->id,
                'changeType' => 'delete',
                'oldValue'   => json_encode($patient->toArray()),
            ]);

            $userDetail->delete();
            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'user_details',
                'primaryKey' => $userDetail->id,
                'changeType' => 'delete',
                'oldValue'   => json_encode($userDetail->toArray()),
            ]);

            User::find($userId)?->delete();
            $this->logChange([
                'logType'    => 'Paciente',
                'table'      => 'users',
                'primaryKey' => $userId,
                'changeType' => 'delete',
            ]);

            DB::commit();
            return response()->json(['message' => 'Paciente eliminado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['message' => 'Error al eliminar el paciente.'], 500);
        }
    }
}
