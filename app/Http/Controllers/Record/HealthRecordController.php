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
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Support\Str;

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

    protected function normalizeForAppendRule(?string $html): string
    {
        $txt = trim(strip_tags((string) $html));
        $txt = preg_replace('/\s+/u', ' ', $txt ?? '');
        return mb_strtolower(trim($txt));
    }
    private function userCan(string $ability): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $teamId = $user->current_team_id
            ?? optional($user->currentTeam)->id
            ?? DB::table('team_user')->where('user_id', $user->id)->value('team_id');

        if (!$teamId) return false;

        return DB::table('team_permissions as tp')
            ->join('permissions as p', 'p.id', '=', 'tp.permission_id')
            ->where('tp.team_id', $teamId)
            ->where('p.slug', $ability)
            ->exists();
    }


    private function resolveRoleKey(?string $raw): ?string
    {
        if (!$raw) return null;
        $rawTrim = trim($raw);
        $roles = (array) config('roles', []);

        if (array_key_exists($rawTrim, $roles)) {
            return $rawTrim;
        }

        foreach ($roles as $key => $cfg) {
            $name = (string) ($cfg['name'] ?? '');
            if (mb_strtolower($name) === mb_strtolower($rawTrim)) {
                return $key;
            }
        }

        return null;
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

        $canFullEdit = $this->userCan('edit_medical_record');

        return Inertia::render('HealthRecords/Create', [
            'patient' => [
                'id'   => $patient->id,
                'uuid' => $patient->user_uuid,
                'name' => $patient->userDetail->name . ' ' . $patient->userDetail->fatherLastName . ' ' . $patient->userDetail->motherLastName,
            ],
            'healthInstitutions' => $healthInstitutions,
            'healthRecord' => $healthRecord ? [
                'healthRecordId'         => $healthRecord->id,
                'cryptHealthRecordId'    => Crypt::encryptString($healthRecord->id),
                'health_institution_id'  => $healthRecord->health_institution_id,
                'medicines'              => $healthRecord->medicines,
                'allergies'              => $healthRecord->allergies,
                'pathologicalBackground' => $healthRecord->pathologicalBackground,
                'laboratoryBackground'   => $healthRecord->laboratoryBackground,
                'nourishmentBackground'  => $healthRecord->nourishmentBackground,
                'medicalInsurance'       => $healthRecord->medicalInsurance,
                'health_institution'     => $healthRecord->health_institution,
                'religion'               => $healthRecord->religion,
            ] : null,
            'permissions' => [
                'can_full_edit' => $this->userCan('edit_medical_record'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validate([
                'health_institution_id'  => 'required|exists:list_health_institutions,id',
                'patient_id'             => 'required|exists:patients,id',
                'medicines'              => 'required|string',
                'allergies'              => 'required|string',
                'pathologicalBackground' => 'required|string',
                'laboratoryBackground'   => 'required|string',
                'nourishmentBackground'  => 'required|string',
                'medicalInsurance'       => 'nullable|string',
                'medical_info'           => 'nullable|string',
                'health_institution'     => 'nullable|string',
                'religion'               => 'nullable|string',
            ]);

            // Seguro
            if (($data['medicalInsurance'] ?? null) === 'Sí') {
                $data['medicalInsurance'] = $data['medical_info'] ?? 'Sí';
            } else {
                $data['medicalInsurance'] = 'No';
            }

            // Institución "Otro" (id=5)
            if ((int) $data['health_institution_id'] === 5) {
                $data['health_institution'] = $data['health_institution'] ?? null;
            } else {
                $data['health_institution'] = null;
            }

            unset($data['medical_info']);

            $year = now()->year;
            $random = strtoupper(Str::random(3));
            $data['record_uuid'] = "EXP{$year}-{$data['patient_id']}{$random}";

            $record = HealthRecord::create($data);

            $this->logChange([
                'logType'      => 'Expediente',
                'table'        => 'health_records',
                'primaryKey'   => $record->id,
                'secondaryKey' => $data['patient_id'] ?? null,
                'changeType'   => 'create',
                'newValue'     => json_encode($record->only([
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
            DB::rollBack();
            Log::error('Error al crear expediente clínico', [
                'request' => $request->all(),
                'error'   => $e->getMessage(),
            ]);

            Log::debug('render permissions', [
                'user_id' => Auth::id(),
                'can_full_edit' => $this->userCan('edit_medical_record'),
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
                'health_institution_id'  => 'required|exists:list_health_institutions,id',
                'medicines'              => 'required|string',
                'allergies'              => 'required|string',
                'pathologicalBackground' => 'required|string',
                'laboratoryBackground'   => 'required|string',
                'nourishmentBackground'  => 'required|string',
                'medicalInsurance'       => 'nullable|string',
                'medical_info'           => 'nullable|string',
                'health_institution'     => 'nullable|string',
                'religion'               => 'nullable|string',
            ]);

            // Seguro
            if (($data['medicalInsurance'] ?? null) === 'Sí') {
                $data['medicalInsurance'] = $data['medical_info'] ?? 'Sí';
            } else {
                $data['medicalInsurance'] = 'No';
            }

            // Institución "Otro" (id=5)
            if ((int) $data['health_institution_id'] === 5) {
                $data['health_institution'] = $data['health_institution'] ?? null;
            } else {
                $data['health_institution'] = null;
            }

            unset($data['medical_info']);

            // Permiso 
            $canFullEdit = $this->userCan('edit_medical_record');

            Log::debug('edit_medical_record', [
                'user_id'       => Auth::id(),
                'team_id'       => Auth::user()?->currentTeam?->id,
                'can_full_edit' => $canFullEdit,
            ]);

            $protectedFields = [
                'medicines',
                'allergies',
                'pathologicalBackground',
                'laboratoryBackground',
                'nourishmentBackground',
            ];

            $before = $healthRecord->replicate();

            if (!$canFullEdit) {
                foreach ($protectedFields as $field) {
                    $incoming = (string) ($data[$field] ?? '');
                    $current  = (string) ($healthRecord->$field ?? '');

                    $incomingClean = $this->normalizeForAppendRule($incoming);
                    $currentClean  = $this->normalizeForAppendRule($current);

                    $isAppendOnly = $currentClean === '' ||
                        ($incomingClean !== '' && Str::startsWith($incomingClean, $currentClean));

                    if (!$isAppendOnly) {
                        $this->logChange([
                            'logType'    => 'Expediente',
                            'table'      => 'health_records',
                            'primaryKey' => $healthRecord->id,
                            'changeType' => 'permission-append-only-block',
                            'fieldName'  => $field,
                            'oldValue'   => $healthRecord->$field,
                            'newValue'   => $data[$field],
                        ]);

                        $data[$field] = $healthRecord->$field;
                    }
                }
            }

            DB::beginTransaction();

            $healthRecord->update($data);

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
                $old = (string) ($before->$campo ?? '');
                $new = (string) ($healthRecord->$campo ?? '');
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
                'record_id' => $healthRecord->id ?? null,
                'error'     => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el expediente.'])
                ->withInput();
        }
    }
}
