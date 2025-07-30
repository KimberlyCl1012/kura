<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\HealthInstitution;
use App\Models\HealthRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Str;

class HealthRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($patientId)
    {
        try {
            $decryptpatientId = Crypt::decryptString($patientId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $healthInstitutions = HealthInstitution::select('id', 'name')->get();
        $healthRecord = HealthRecord::where('patient_id', $decryptpatientId)->first();
        $patient = Patient::with('userDetail')
            ->where('id', $decryptpatientId)
            ->firstOrFail();



        return Inertia::render('HealthRecords/Create', [
            'patient' => [
                'id' => $patient->id,
                'uuid' => $patient->user_uuid,
                'name' => $patient->userDetail->name . ' ' . $patient->userDetail->fatherLastName . ' ' . $patient->userDetail->motherLastName,
            ],
            'healthInstitutions' => $healthInstitutions,
            'healthRecord' => $healthRecord,
            'permissions' => [
                'editor_edit_all_denied' => Auth::user()->hasExplicitlyDenied('editor:edit-all'),
                'editor_edit_all_allowed' => Auth::user()->can('editor:edit-all'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'health_institution_id' => 'required|exists:list_health_institutions,id',
                'patient_id' => 'required|exists:patients,id',
                'medicines' => 'required|string',
                'allergies' => 'required|string',
                'pathologicalBackground' => 'required|string',
                'laboratoryBackground' => 'required|string',
                'nourishmentBackground' => 'required|string',
                'medicalInsurance' => 'nullable|string',
                'medical_info' => 'nullable|string',
                'health_institution' => 'nullable|string',
                'religion' => 'nullable|string',
            ]);

            if ($data['medicalInsurance'] === 'Sí') {
                $data['medicalInsurance'] = $data['medical_info'] ?? 'Sí';
            } else {
                $data['medicalInsurance'] = 'No';
            }

            if ($data['health_institution_id'] == 5) {
                $data['health_institution'] = $data['health_institution'] ?? null;
            } else {
                $data['health_institution'] = null;
            }

            unset($data['medical_info']);

            $year = now()->year;
            $random = strtoupper(Str::random(3));
            $data['record_uuid'] = "EXP{$year}-{$data['patient_id']}{$random}";

            HealthRecord::create($data);

            return redirect()->back()->with('success', 'Expediente guardado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Crear expediente');
            Log::debug($e);
            DB::rollBack();
            Log::error('Error al crear expediente clínico', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Ocurrió un error al guardar el expediente.'])
                ->withInput();
        }
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
        try {
            $data = $request->validate([
                'health_institution_id' => 'required|exists:list_health_institutions,id',
                'medicines' => 'required|string',
                'allergies' => 'required|string',
                'pathologicalBackground' => 'required|string',
                'laboratoryBackground' => 'required|string',
                'nourishmentBackground' => 'required|string',
                'medicalInsurance' => 'nullable|string',
                'medical_info' => 'nullable|string',
                'health_institution' => 'nullable|string',
                'religion' => 'nullable|string',
            ]);

            if ($data['medicalInsurance'] === 'Sí') {
                $data['medicalInsurance'] = $data['medical_info'] ?? 'Sí';
            } else {
                $data['medicalInsurance'] = 'No';
            }

            if ($data['health_institution_id'] == 5) {
                $data['health_institution'] = $data['health_institution'] ?? null;
            } else {
                $data['health_institution'] = null;
            }

            unset($data['medical_info']);

            $user = auth()->user();
            $isDenied = method_exists($user, 'hasExplicitlyDenied') && $user->hasExplicitlyDenied('editor:edit-all');
            $fullEdit = !$isDenied;

            Log::debug('Permiso explícitamente denegado:', [
                'denegado' => $isDenied,
                'permite_edicion_total' => $fullEdit,
            ]);

            $protectedFields = [
                'medicines',
                'allergies',
                'pathologicalBackground',
                'laboratoryBackground',
                'nourishmentBackground'
            ];

            foreach ($protectedFields as $field) {
                if (!$fullEdit && strlen(strip_tags($data[$field])) < strlen(strip_tags($healthRecord->$field))) {
                    $data[$field] = $healthRecord->$field;
                }
            }

            $healthRecord->update($data);

            return redirect()->back()->with('success', 'Expediente actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::info('Editar expediente clínico');
            Log::debug($e);
            Log::error('Error al actualizar expediente clínico', [
                'record_id' => $healthRecord->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el expediente.'])
                ->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
