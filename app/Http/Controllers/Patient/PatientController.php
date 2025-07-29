<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
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
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:user_details,contactEmail',
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
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt('kura+123'),
            ]);

            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'contactEmail' => $request->email,
                'mobile' => $request->mobile,
                'sex' => $request->sex,
                'site_id' => $request->site_id,
            ]);

            $anio = Carbon::now()->format('Y'); // Año actual, ej. 2025
            $site = str_pad($userDetail->site_id, 2, '0', STR_PAD_LEFT); // Asegura que el site tenga 2 dígitos
            $random = Str::upper(Str::random(2)); // 2 caracteres aleatorios

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
                'relativeMobile' => $request->relativeMobile,
                'consent' => $request->has('consent') ? (bool) $request->consent : false,
            ]);

            DB::commit();

            return redirect()->route('patients.index')->with('success', 'Paciente creado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Crear paciente');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al crear el paciente.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
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
            $patient = Patient::findOrFail($id);
            $userDetail = $patient->userDetail;

            // Actualizar tabla users directamente sin relación
            DB::table('users')
                ->where('id', $userDetail->user_id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            // Actualizar tabla user_details
            DB::table('user_details')
                ->where('id', $userDetail->id)
                ->update([
                    'name' => $request->name,
                    'fatherLastName' => $request->fatherLastName,
                    'motherLastName' => $request->motherLastName,
                    'contactEmail' => $request->email,
                    'mobile' => $request->mobile,
                    'sex' => $request->sex,
                    'site_id' => $request->site_id,
                    'updated_at' => now(),
                ]);

            // Actualizar tabla patients
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
                'relativeMobile' => $request->relativeMobile,
            ]);

            DB::commit();

            return redirect()->route('patients.index')->with('success', 'Paciente actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Editar paciente');
            Log::debug($e);
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
            // Eliminar paciente
            $patient->delete();
            // Eliminar userDetail
            $userDetail->delete();
            // Eliminar usuario
            DB::table('users')->where('id', $userId)->delete();
            DB::commit();

            return back()->with('success', 'Paciente eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Eliminar paciente');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al eliminar el paciente.']);
        }
    }

    public function show($id)
    {
        $patient = Patient::with('userDetail')->findOrFail($id);

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
