<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\Measurement;
use App\Models\Media;
use App\Models\Method;
use App\Models\Submethod;
use App\Models\Treatment;
use App\Models\Wound;
use App\Models\WoundAssessment;
use App\Models\WoundFollow;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WoundFollowController extends Controller
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

    public function edit($appointmentId, $woundId)
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
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->where('wounds.id', $decryptWoundId)
            ->firstOrFail();

        $measurement = Measurement::where('wound_id', $decryptWoundId)->first();

        $mediaHistory = Media::where('wound_id', $decryptWoundId)
            ->get(['id', 'content', 'position']);

        $mediaFollow = Media::where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->get(['id', 'content', 'position']);

        $treatmentsHistory = Treatment::with([
            'methods:id,treatment_id,treatment_method_id',
            'submethods:id,treatment_id,treatment_submethod_id,treatment_method_id'
        ])
            ->where('wound_id', $decryptWoundId)
            ->get();

        $treatmentFollow = Treatment::with([
            'methods:id,treatment_id,treatment_method_id',
            'submethods:id,treatment_id,treatment_submethod_id,treatment_method_id'
        ])
            ->where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->first();

        $follow = WoundFollow::where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->first();

        return Inertia::render('WoundsFollow/Edit', [
            'woundId' => $woundId,
            'appointmentId' => $appointmentId,
            'wound' => $wound,
            'follow' => $follow,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'treatmentFollow' => $treatmentFollow,
            'measurement' => $measurement,
            'existingImagesHistory' => $mediaHistory,
            'existingImagesFollow' => $mediaFollow,
            'treatmentMethods' => Method::where('state', 1)
                ->with(['submethods' => function ($q) {
                    $q->where('state', 1);
                }])
                ->get(),

            'treatmentSubmethods' => Submethod::where('state', 1)->get(),
            'treatmentsHistory' => $treatmentsHistory,
            'assessments' => WoundAssessment::where('state', 1)
                ->select('type', 'name')
                ->orderBy('type')->orderBy('name', 'ASC')
                ->get()
                ->groupBy('type')
                ->map(fn($g) => $g->pluck('name'))
                ->toArray(),
        ]);
    }

    public function update(Request $request, $woundId)
    {
        try {
            $decryptedWoundId = Crypt::decryptString($woundId);
            $request->merge(['wound_id' => $decryptedWoundId]);

            $validated = $request->validate([
                'wound_id' => 'required|exists:wounds,id',
                'appointment_id' => 'required|exists:appointments,id',
                'wound_type_id' => 'required|exists:list_wound_types,id',
                'wound_subtype_id' => 'required|exists:list_wound_subtypes,id',
                'body_location_id' => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'grade_foot' => 'nullable|string|max:255',
                'MESI' => 'nullable|string|max:255',
                'borde' => 'nullable|string|max:255',
                'edema' => 'nullable|string|max:255',
                'dolor' => 'nullable|string|max:255',
                'exudado_cantidad' => 'nullable|string|max:255',
                'exudado_tipo' => 'nullable|string|max:255',
                'olor' => 'nullable|string|max:255',
                'piel_perilesional' => 'nullable|array',
                'infeccion' => 'nullable|array',
                'tipo_dolor' => 'nullable|string|max:255',
                'duracion_dolor' => 'nullable|string|max:255',
                'visual_scale' => 'nullable|string|max:255',
                'ITB_izquierdo' => 'nullable|string|max:255',
                'pulse_dorsal_izquierdo' => 'nullable|string|max:255',
                'pulse_tibial_izquierdo' => 'nullable|string|max:255',
                'pulse_popliteo_izquierdo' => 'nullable|string|max:255',
                'ITB_derecho' => 'nullable|string|max:255',
                'pulse_dorsal_derecho' => 'nullable|string|max:255',
                'pulse_tibial_derecho' => 'nullable|string|max:255',
                'pulse_popliteo_derecho' => 'nullable|string|max:255',
                'monofilamento' => 'nullable|string|max:255',
                'blood_glucose' => 'nullable|string|max:255',
                'measurementDate' => 'required|date',
                'length' => 'nullable|numeric',
                'width' => 'nullable|numeric',
                'area' => 'nullable|numeric',
                'depth' => 'nullable|numeric',
                'volume' => 'nullable|numeric',
                'tunneling' => 'nullable|string|max:255',
                'undermining' => 'nullable|string|max:255',
                'granulation_percent' => 'nullable|numeric',
                'slough_percent' => 'nullable|numeric',
                'necrosis_percent' => 'nullable|numeric',
                'epithelialization_percent' => 'nullable|numeric',
                'note' => 'nullable|string',
            ]);

            // Normalizar arrays a JSON para guardar
            $validated['piel_perilesional'] = json_encode($request->piel_perilesional ?? []);
            $validated['infeccion']         = json_encode($request->infeccion ?? []);

            DB::beginTransaction();

            $follow = WoundFollow::firstOrNew([
                'wound_id'       => $validated['wound_id'],
                'appointment_id' => $validated['appointment_id'],
            ]);

            $isCreate = $follow->exists === false;
            $before   = $follow->exists ? $follow->replicate() : null;

            $follow->fill($validated);
            $follow->save();

            if ($isCreate) {
                // Log de creación
                $this->logChange([
                    'logType'      => 'Seguimiento',
                    'table'        => 'wound_follows',
                    'primaryKey'   => $follow->id,
                    'secondaryKey' => $validated['wound_id'],
                    'changeType'   => 'create',
                    'newValue'     => json_encode($follow->only([
                        'id',
                        'wound_id',
                        'appointment_id',
                        'wound_type_id',
                        'wound_subtype_id',
                        'body_location_id',
                        'body_sublocation_id',
                        'grade_foot',
                        'MESI',
                        'borde',
                        'edema',
                        'dolor',
                        'exudado_cantidad',
                        'exudado_tipo',
                        'olor',
                        'piel_perilesional',
                        'infeccion',
                        'tipo_dolor',
                        'duracion_dolor',
                        'visual_scale',
                        'ITB_izquierdo',
                        'pulse_dorsal_izquierdo',
                        'pulse_tibial_izquierdo',
                        'pulse_popliteo_izquierdo',
                        'ITB_derecho',
                        'pulse_dorsal_derecho',
                        'pulse_tibial_derecho',
                        'pulse_popliteo_derecho',
                        'monofilamento',
                        'blood_glucose',
                        'measurementDate',
                        'length',
                        'width',
                        'area',
                        'depth',
                        'volume',
                        'tunneling',
                        'undermining',
                        'granulation_percent',
                        'slough_percent',
                        'necrosis_percent',
                        'epithelialization_percent',
                        'note',
                        'created_at'
                    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            } else {
                // Log campo por campo (incluye arrays como JSON string)
                $fields = [
                    'wound_type_id',
                    'wound_subtype_id',
                    'body_location_id',
                    'body_sublocation_id',
                    'grade_foot',
                    'MESI',
                    'borde',
                    'edema',
                    'dolor',
                    'exudado_cantidad',
                    'exudado_tipo',
                    'olor',
                    'piel_perilesional',
                    'infeccion',
                    'tipo_dolor',
                    'duracion_dolor',
                    'visual_scale',
                    'ITB_izquierdo',
                    'pulse_dorsal_izquierdo',
                    'pulse_tibial_izquierdo',
                    'pulse_popliteo_izquierdo',
                    'ITB_derecho',
                    'pulse_dorsal_derecho',
                    'pulse_tibial_derecho',
                    'pulse_popliteo_derecho',
                    'monofilamento',
                    'blood_glucose',
                    'measurementDate',
                    'length',
                    'width',
                    'area',
                    'depth',
                    'volume',
                    'tunneling',
                    'undermining',
                    'granulation_percent',
                    'slough_percent',
                    'necrosis_percent',
                    'epithelialization_percent',
                    'note',
                ];

                foreach ($fields as $field) {
                    $old = $before->$field ?? null;
                    $new = $follow->$field ?? null;

                    // Para arrays JSON
                    $isJsonArray = in_array($field, ['piel_perilesional', 'infeccion'], true);
                    if ($isJsonArray) {
                        $oldStr = is_null($old) ? null : (is_array($old) ? json_encode($old) : (string)$old);
                        $newStr = is_null($new) ? null : (is_array($new) ? json_encode($new) : (string)$new);

                        if ($oldStr !== $newStr) {
                            $this->logChange([
                                'logType'    => 'Seguimiento',
                                'table'      => 'wound_follows',
                                'primaryKey' => $follow->id,
                                'changeType' => 'update',
                                'fieldName'  => $field,
                                'oldValue'   => $oldStr,
                                'newValue'   => $newStr,
                            ]);
                        }
                    } else {
                        if ((string)$old !== (string)$new) {
                            $this->logChange([
                                'logType'    => 'Seguimiento',
                                'table'      => 'wound_follows',
                                'primaryKey' => $follow->id,
                                'changeType' => 'update',
                                'fieldName'  => $field,
                                'oldValue'   => $old,
                                'newValue'   => $new,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', $isCreate
                ? 'Seguimiento creado correctamente.'
                : 'Seguimiento actualizado correctamente.');
        } catch (DecryptException $e) {
            DB::rollBack();
            Log::error('Error al desencriptar el woundId', [
                'wound_id_raw' => $woundId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'ID de herida inválido o manipulado.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error general al guardar seguimiento', [
                'wound_id' => $woundId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al guardar el seguimiento.',
            ]);
        }
    }
}
