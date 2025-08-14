<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
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
    protected function logChange(array $data): void
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
            'healthRecord' => $healthRecord ? [
                'healthRecordId' => $healthRecord->id,
                'cryptHealthRecordId' => Crypt::encryptString($healthRecord->id),
                'health_institution_id' => $healthRecord->health_institution_id,
                'medicines' => $healthRecord->medicines,
                'allergies' => $healthRecord->allergies,
                'pathologicalBackground' => $healthRecord->pathologicalBackground,
                'laboratoryBackground' => $healthRecord->laboratoryBackground,
                'nourishmentBackground' => $healthRecord->nourishmentBackground,
                'medicalInsurance' => $healthRecord->medicalInsurance,
                'health_institution' => $healthRecord->health_institution,
                'religion' => $healthRecord->religion,
            ] : null,
            'permissions' => [
                'editor_edit_all_denied' => Auth::user()->hasExplicitlyDenied('editor:edit-all'),
                'editor_edit_all_allowed' => Auth::user()->can('editor:edit-all'),
            ],
        ]);
    }

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

            $record = HealthRecord::create($data);

            // Log de creación
            $this->logChange([
                'logType'    => 'Expediente',
                'table'      => 'health_records',
                'primaryKey' => $record->id,
                'secondaryKey' => $data['patient_id'] ?? null,
                'changeType' => 'create',
                'newValue'   => json_encode($record->only([
                    'id',
                    'record_uuid',
                    'patient_id',
                    'health_institution_id',
                    'health_institution',
                    'medicines',
                    'allergies',
                    'pathologicalBackground',
                    'laboratoryBackground',
                    'nourishmentBackground',
                    'medicalInsurance',
                    'religion',
                    'created_at',
                ])),
            ]);

            DB::commit();

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

            // Guardamos antes para comparar y loguear
            $before = $healthRecord->replicate();

            foreach ($protectedFields as $field) {
                $newClean = strlen(strip_tags($data[$field] ?? ''));
                $oldClean = strlen(strip_tags($healthRecord->$field ?? ''));

                if (!$fullEdit && $newClean < $oldClean) {
                    // Log del bloqueo de recorte
                    $this->logChange([
                        'logType'    => 'Expediente',
                        'table'      => 'health_records',
                        'primaryKey' => $healthRecord->id,
                        'changeType' => 'permission-override',
                        'fieldName'  => $field,
                        'oldValue'   => $healthRecord->$field,
                        'newValue'   => $data[$field],
                    ]);

                    // Revertimos al valor anterior
                    $data[$field] = $healthRecord->$field;
                }
            }

            DB::beginTransaction();

            $healthRecord->update($data);

            // Log campo por campo solo si cambió
            $campos = [
                'health_institution_id',
                'health_institution',
                'medicines',
                'allergies',
                'pathologicalBackground',
                'laboratoryBackground',
                'nourishmentBackground',
                'medicalInsurance',
                'religion',
            ];

            foreach ($campos as $campo) {
                $old = (string)($before->$campo ?? '');
                $new = (string)($healthRecord->$campo ?? '');
                if ($old !== $new) {
                    $this->logChange([
                        'logType'    => 'Expediente',
                        'table'      => 'health_records',
                        'primaryKey' => $healthRecord->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $old,
                        'newValue'   => $new,
                    ]);
                }
            }

            DB::commit();

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
}
