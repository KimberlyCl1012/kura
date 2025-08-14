<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\Measurement;
use App\Models\Method;
use App\Models\Patient;
use App\Models\Site;
use App\Models\State;
use App\Models\Submethod;
use App\Models\Treatment;
use App\Models\Wound;
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundController extends Controller
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

    public function index(Request $request, $appointmentId, $healthRecordId)
    {
        try {
            $decryptAppointmentId = Crypt::decryptString($appointmentId);
            $decryptHealthRecordId = Crypt::decryptString($healthRecordId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $wounds = Wound::select([
            'wounds.*',
            'list_wound_types.name as wound_type',
            'list_wound_types.id as wound_type_id',
            'list_wound_subtypes.name as wound_subtype',
            'list_wound_subtypes.id as wound_subtype_id',
            'list_body_locations.name as body_location',
            'list_body_locations.id as body_location_id',
            'list_body_sublocations.name as body_sublocation',
            'list_body_sublocations.id as body_sublocation_id',
            'list_wound_phases.name as wound_phase',
            'list_wound_phases.id as wound_phase_id',
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wounds.wound_phase_id')
            ->where('wounds.appointment_id', $decryptAppointmentId)
            ->where('wounds.health_record_id', $decryptHealthRecordId)
            ->where('wounds.state', 1)
            ->get();


        foreach ($wounds as $wound) {
            $wound->wound_id = Crypt::encryptString($wound->id);

            $histories = DB::table('wound_histories')
                ->select([
                    'wound_histories.*',
                    'list_wound_types.name as wound_type_history',
                    'list_wound_subtypes.name as wound_subtype_history',
                    'list_body_locations.name as body_location_history',
                    'list_body_sublocations.name as body_sublocation_history',
                    'list_wound_phases.name as wound_phase_history',
                ])
                ->join('list_wound_types', 'list_wound_types.id', '=', 'wound_histories.wound_type_id')
                ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wound_histories.wound_subtype_id')
                ->join('list_body_locations', 'list_body_locations.id', '=', 'wound_histories.body_location_id')
                ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wound_histories.body_sublocation_id')
                ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wound_histories.wound_phase_id')
                ->where('wound_histories.wound_id', $wound->id)
                ->where('wound_histories.state', 1)
                ->get()
                ->map(function ($h) use ($wound) {
                    $h->wound_history_id = Crypt::encryptString($h->id);
                    return $h;
                });

            $wound->histories = $histories;
        }

        $followUps = Wound::select([
            'wounds.*',
            'list_wound_types.name as wound_type',
            'list_wound_types.id as wound_type_id',
            'list_wound_subtypes.name as wound_subtype',
            'list_wound_subtypes.id as wound_subtype_id',
            'list_body_locations.name as body_location',
            'list_body_locations.id as body_location_id',
            'list_body_sublocations.name as body_sublocation',
            'list_body_sublocations.id as body_sublocation_id',
            'list_wound_phases.name as wound_phase',
            'list_wound_phases.id as wound_phase_id',
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wounds.wound_phase_id')
            ->where('wounds.health_record_id', $decryptHealthRecordId)
            ->where('wounds.state', 2) // Seguimientos
            ->get();

        foreach ($followUps as $wound) {
            $wound->wound_id = Crypt::encryptString($wound->id);
        }


        // Obtener paciente
        $healthRecord = HealthRecord::with('patient.userDetail')->findOrFail($decryptHealthRecordId);
        $patient = $healthRecord->patient;
        $fullName = $patient && $patient->userDetail
            ? "{$patient->userDetail->name} {$patient->userDetail->fatherLastName} {$patient->userDetail->motherLastName}"
            : 'Nombre no disponible';

        return Inertia::render('Wounds/Index', [
            'wounds' => $wounds,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'woundsPhase' => WoundPhase::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'appointmentId' => $decryptAppointmentId,
            'healthRecordId' => $decryptHealthRecordId,
            'followUps' => $followUps,
            'patient' => [
                'id' => $patient->id,
                'full_name' => $fullName,
            ],
        ]);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'appointment_id'      => 'required|exists:appointments,id',
                'health_record_id'    => 'required|exists:health_records,id',
                'wound_type_id'       => 'required|exists:list_wound_types,id',
                'wound_subtype_id'    => 'required|exists:list_wound_subtypes,id',
                'body_location_id'    => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'wound_phase_id'      => 'required|exists:list_wound_phases,id',
                'woundBeginDate'      => 'required|date',
            ];

            $validator = Validator::make($request->all(), $rules);

            $validator->sometimes('grade_foot', 'required', function ($input) {
                return $input->wound_type_id == 8;
            });

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $wound = Wound::create([
                'appointment_id'      => $request->appointment_id,
                'health_record_id'    => $request->health_record_id,
                'wound_type_id'       => $request->wound_type_id,
                'grade_foot'          => $request->grade_foot,
                'wound_subtype_id'    => $request->wound_subtype_id,
                'body_location_id'    => $request->body_location_id,
                'body_sublocation_id' => $request->body_sublocation_id,
                'wound_phase_id'      => $request->wound_phase_id,
                'woundBeginDate'      => Carbon::parse($request->woundBeginDate)->format('Y-m-d'),
            ]);

            // Log de creación
            $this->logChange([
                'logType'      => 'Herida',
                'table'        => 'wounds',
                'primaryKey'   => $wound->id,
                'secondaryKey' => $request->health_record_id,
                'changeType'   => 'create',
                'newValue'     => json_encode($wound->only([
                    'appointment_id',
                    'health_record_id',
                    'wound_type_id',
                    'grade_foot',
                    'wound_subtype_id',
                    'body_location_id',
                    'body_sublocation_id',
                    'wound_phase_id',
                    'woundBeginDate',
                    'state',
                ])),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Herida creada exitosamente',
                'wound'   => $wound,
            ]);
        } catch (\Exception $e) {
            Log::info('Crear herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'message' => 'Error inesperado al guardar la herida',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($woundId)
    {
        try {
            $decryptWoundId = Crypt::decryptString($woundId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $wound = Wound::select([
            'wounds.*',
            'list_wound_types.name as wound_type_name',
            'list_wound_types.id as wound_type_id',
            'list_wound_subtypes.name as wound_subtype_name',
            'list_wound_subtypes.id as wound_subtype_id',
            'list_body_locations.name as body_location_name',
            'list_body_locations.id as body_location_id',
            'list_body_sublocations.name as body_sublocation_name',
            'list_body_sublocations.id as body_sublocation_id',
            'list_wound_phases.name as wound_phase_name',
            'list_wound_phases.id as wound_phase_id',
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wounds.wound_phase_id')
            ->where('wounds.id', $decryptWoundId)
            ->where('wounds.state', 1)
            ->firstOrFail();

        $wound->wound_id = Crypt::encryptString($wound->id);

        $measurement = Measurement::where('wound_id', $decryptWoundId)->first();

        $treatment = Treatment::with([
            'methods:id,treatment_id,treatment_method_id',
            'submethods:id,treatment_id,treatment_submethod_id,treatment_method_id'
        ])->where('wound_id', $decryptWoundId)->first();

        return Inertia::render('Wounds/Edit', [
            'wound' => $wound,
            'measurement' => $measurement,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'woundsPhase' => WoundPhase::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'treatmentMethods' => Method::where('state', 1)
                ->with(['submethods' => function ($q) {
                    $q->where('state', 1);
                }])
                ->get(),

            'treatmentSubmethods' => Submethod::where('state', 1)->get(),
            'treatment' => $treatment,
        ]);
    }

    public function update(Request $request, Wound $wound)
    {
        try {
            $vascularRequired = in_array($request->body_location_id, range(18, 33));

            $rules = [
                'wound_type_id' => 'required|exists:list_wound_types,id',
                'wound_subtype_id' => 'required|exists:list_wound_subtypes,id',
                'body_location_id' => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'wound_phase_id' => 'required|exists:list_wound_phases,id',
                'woundBeginDate' => 'nullable|date',
                'woundHealthDate' => 'nullable|date',
                'grade_foot' => 'nullable',
                'valoracion' => 'nullable|string|max:255',
                'MESI' => 'nullable|string|max:255',
                'woundBackground' => 'nullable|string|max:255',
                'borde' => 'nullable|string|max:255',
                'edema' => 'nullable|string|max:255',
                'dolor' => 'nullable|string|max:255',
                'exudado_cantidad' => 'nullable|string|max:255',
                'exudado_tipo' => 'nullable|string|max:255',
                'olor' => 'nullable|string|max:255',
                'piel_perilesional' => 'nullable|array',
                'infeccion' => 'nullable|array',
                'tipo_dolor' => 'nullable|string|max:255',
                'visual_scale' => 'nullable|string|max:255',
                'monofilamento' => 'nullable|string|max:255',
                'blood_glucose' => 'nullable|string|max:255',
            ];

            if ($request->wound_type_id == 8) {
                $rules['grade_foot'] = 'required';
            }

            if ($vascularRequired) {
                $rules = array_merge($rules, [
                    'valoracion' => 'required|string|max:255',
                    'ITB_derecho' => 'required|string|max:255',
                    'pulse_dorsal_derecho' => 'required|string|max:255',
                    'pulse_tibial_derecho' => 'required|string|max:255',
                    'pulse_popliteo_derecho' => 'required|string|max:255',
                    'ITB_izquierdo' => 'required|string|max:255',
                    'pulse_dorsal_izquierdo' => 'required|string|max:255',
                    'pulse_tibial_izquierdo' => 'required|string|max:255',
                    'pulse_popliteo_izquierdo' => 'required|string|max:255',
                ]);
            }

            $validated = $request->validate($rules);

            // Normalizar fechas si vienen
            foreach (['woundBeginDate', 'woundHealthDate'] as $dateField) {
                if (!empty($validated[$dateField])) {
                    $validated[$dateField] = Carbon::parse($validated[$dateField])->format('Y-m-d');
                }
            }

            // Guardamos snapshot para comparar
            $before = $wound->replicate();

            DB::beginTransaction();

            $wound->update($validated);

            // Campos a auditar (incluye arrays -> JSON)
            $fields = [
                'wound_type_id',
                'wound_subtype_id',
                'body_location_id',
                'body_sublocation_id',
                'wound_phase_id',
                'woundBeginDate',
                'woundHealthDate',
                'grade_foot',
                'valoracion',
                'MESI',
                'woundBackground',
                'borde',
                'edema',
                'dolor',
                'exudado_cantidad',
                'exudado_tipo',
                'olor',
                'piel_perilesional',
                'infeccion',
                'tipo_dolor',
                'visual_scale',
                'monofilamento',
                'blood_glucose',
            ];

            foreach ($fields as $field) {
                $old = $before->$field ?? null;
                $new = $wound->$field ?? null;

                // Si son arrays (o json), los comparamos stringificados
                $isArrayLike = in_array($field, ['piel_perilesional', 'infeccion'], true);
                if ($isArrayLike) {
                    $old = $old ? (is_array($old) ? $old : json_decode($old, true)) : null;
                    $new = $new ? (is_array($new) ? $new : json_decode($new, true)) : null;

                    $oldStr = json_encode($old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $newStr = json_encode($new, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    if ($oldStr !== $newStr) {
                        $this->logChange([
                            'logType'    => 'Herida',
                            'table'      => 'wounds',
                            'primaryKey' => $wound->id,
                            'changeType' => 'update',
                            'fieldName'  => $field,
                            'oldValue'   => $oldStr,
                            'newValue'   => $newStr,
                        ]);
                    }
                } else {
                    // Comparación simple como string
                    if ((string)$old !== (string)$new) {
                        $this->logChange([
                            'logType'    => 'Herida',
                            'table'      => 'wounds',
                            'primaryKey' => $wound->id,
                            'changeType' => 'update',
                            'fieldName'  => $field,
                            'oldValue'   => $old,
                            'newValue'   => $new,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Herida actualizada correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::info('Actualizar herida');
            Log::debug($e);
            Log::error('Error al actualizar herida', [
                'wound_id' => $wound->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar la herida.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
