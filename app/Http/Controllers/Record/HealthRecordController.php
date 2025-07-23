<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\HealthInstitution;
use App\Models\HealthRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        return redirect()->back();
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
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

        /** 🔐 Verificación de permisos para editar libremente */
        $user = auth()->user();
        $permissions = is_array($user->current_team_role_permissions) ? $user->current_team_role_permissions : [];
        $fullEdit = in_array('*', $permissions) || in_array('editor:edit-all', $permissions);


        $protectedFields = ['medicines', 'allergies', 'pathologicalBackground', 'laboratoryBackground', 'nourishmentBackground'];

        foreach ($protectedFields as $field) {
            if (!$fullEdit) {
                // si el nuevo texto es más corto que el anterior, se sospecha eliminación
                if (strlen($data[$field]) < strlen($healthRecord->$field)) {
                    // evitamos el borrado
                    $data[$field] = $healthRecord->$field;
                }
            }
        }

        $healthRecord->update($data);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
