<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\WoundAssessment;
use App\Models\WoundHistory;
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundHistoryController extends Controller
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

    protected function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
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

    // Normaliza HTML a texto plano compacto para comparar “append-only”
    private function normalizeForAppend(?string $html): string
    {
        $txt = strip_tags((string) $html);
        $txt = preg_replace('/\xc2\xa0/', ' ', $txt);    // nbsp
        $txt = preg_replace('/\s+/u', ' ', trim($txt));  // colapsar espacios
        return mb_strtolower($txt, 'UTF-8');
    }


    public function store(Request $request)
    {
        try {
            $rules = [
                'health_record_id'    => 'required|exists:health_records,id',
                'wound_type_id'       => 'required|exists:list_wound_types,id',
                'grade_foot'          => 'nullable|integer',
                'type_bite'          => 'nullable',
                'wound_subtype_id'    => 'required|exists:list_wound_subtypes,id',
                'body_location_id'    => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'wound_phase_id'      => 'required|exists:list_wound_phases,id',
                'woundBeginDate'      => 'required|date',
            ];

            $validator = Validator::make($request->all(), $rules);

            // Si tipo de herida es 8 => grade_foot requerido
            $validator->sometimes('grade_foot', 'required', function ($input) {
                return (int) $input->wound_type_id === 8;
            });

            $validator->sometimes('type_bite', 'required', function ($input) {
                return $input->wound_subtype_id == 10;
            });

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $history = WoundHistory::create([
                'wound_id'            => $request->wound_id,
                'wound_type_id'       => $request->wound_type_id,
                'grade_foot'          => $request->grade_foot,
                'type_bite'          => $request->type_bite,
                'wound_subtype_id'    => $request->wound_subtype_id,
                'body_location_id'    => $request->body_location_id,
                'body_sublocation_id' => $request->body_sublocation_id,
                'wound_phase_id'      => $request->wound_phase_id,
                'woundBeginDate'      => $this->parseDate($request->woundBeginDate),
                'woundBackground'     => 1,
            ]);

            // AUDITORÍA: create
            $this->logChange([
                'logType'      => 'WoundHistory',
                'table'        => 'wound_histories',
                'primaryKey'   => $history->id,
                'secondaryKey' => $history->wound_id,
                'changeType'   => 'create',
                'newValue'     => json_encode([
                    'id'                  => $history->id,
                    'wound_id'            => $history->wound_id,
                    'wound_type_id'       => $history->wound_type_id,
                    'grade_foot'          => $history->grade_foot,
                    'type_bite'          => $history->type_bite,
                    'wound_subtype_id'    => $history->wound_subtype_id,
                    'body_location_id'    => $history->body_location_id,
                    'body_sublocation_id' => $history->body_sublocation_id,
                    'wound_phase_id'      => $history->wound_phase_id,
                    'woundBeginDate'      => $history->woundBeginDate,
                    'woundBackground'     => $history->woundBackground,
                    'created_at'          => optional($history->created_at)->toDateTimeString(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Antecedente de herida guardado correctamente',
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Crear antecedente de la herida');
            Log::debug($e);
            Log::error($e);
            return response()->json([
                'message' => 'Error inesperado al guardar el antecedente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($woundHisId)
    {
        try {
            $decryptHistoryId = Crypt::decryptString($woundHisId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $history = WoundHistory::findOrFail($decryptHistoryId);

        return Inertia::render('WoundsHis/Edit', [
            'woundHistory'    => $history,
            'woundsType'      => WoundType::where('state', 1)->get(),
            'woundsSubtype'   => WoundSubtype::where('state', 1)->get(),
            'woundsPhase'     => WoundPhase::where('state', 1)->get(),
            'bodyLocations'   => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'assessments' => WoundAssessment::where('state', 1)
                ->select('type', 'name')
                ->orderBy('type')->orderBy('name', 'ASC')
                ->get()
                ->groupBy('type')
                ->map(fn($g) => $g->pluck('name'))
                ->toArray(),
        ]);
    }

    public function update(Request $request, $woundHisId)
    {
        try {
            $history = WoundHistory::findOrFail($woundHisId);

            // -------- Reglas --------
            $rules = [
                'wound_type_id'            => 'required|exists:list_wound_types,id',
                'grade_foot'               => 'nullable|integer',
                'type_bite'                => 'nullable|string|max:255',
                'wound_subtype_id'         => 'required|exists:list_wound_subtypes,id',
                'body_location_id'         => 'required|exists:list_body_locations,id',
                'body_sublocation_id'      => 'required|exists:list_body_sublocations,id',
                'wound_phase_id'           => 'required|exists:list_wound_phases,id',
                'woundBeginDate'           => 'required|date',
                'woundHealthDate'          => 'nullable|date',
                'measurementDate'          => 'nullable|date',
                'valoracion'               => 'nullable|string|max:255', // si lo usas para lógica
                'MESI'                     => 'nullable|string|max:255',
                'borde'                    => 'nullable|string|max:255',
                'edema'                    => 'nullable|string|max:255',
                'dolor'                    => 'nullable|string|max:255',
                'duracion_dolor'                    => 'nullable|string|max:255',
                'exudado_cantidad'         => 'nullable|string|max:255',
                'exudado_tipo'             => 'nullable|string|max:255',
                'olor'                     => 'nullable|string|max:255',
                'piel_perilesional'        => 'nullable|array',
                'infeccion'                => 'nullable|array',
                'tipo_dolor'               => 'nullable|string|max:255',
                'visual_scale'             => 'nullable|string|max:255',
                'monofilamento'            => 'nullable|string|max:255',
                'blood_glucose'            => 'nullable|string|max:255',
                'ITB_izquierdo'            => 'nullable|string|max:255',
                'pulse_dorsal_izquierdo'   => 'nullable|string|max:255',
                'pulse_tibial_izquierdo'   => 'nullable|string|max:255',
                'pulse_popliteo_izquierdo' => 'nullable|string|max:255',
                'ITB_derecho'              => 'nullable|string|max:255',
                'pulse_dorsal_derecho'     => 'nullable|string|max:255',
                'pulse_tibial_derecho'     => 'nullable|string|max:255',
                'pulse_popliteo_derecho'   => 'nullable|string|max:255',
                'length'                   => 'nullable|numeric',
                'width'                    => 'nullable|numeric',
                'area'                     => 'nullable|numeric',
                'depth'                    => 'nullable|numeric',
                'volume'                   => 'nullable|numeric',
                'tunneling'                => 'nullable|string|max:255',
                'undermining'              => 'nullable|string|max:255',
                'granulation_percent'      => 'nullable|numeric',
                'slough_percent'           => 'nullable|numeric',
                'necrosis_percent'         => 'nullable|numeric',
                'epithelialization_percent' => 'nullable|numeric',
                'description'              => 'required|string|min:1',
            ];

            $validator = Validator::make($request->all(), $rules);
            // Reglas condicionales
            $validator->sometimes('grade_foot', 'required', fn($input) => (int) $input->wound_type_id === 8);
            $validator->sometimes('type_bite', 'required', fn($input) => (int) $input->wound_subtype_id === 10);

            // Evitar descripción vacía con solo etiquetas
            $plain = trim(preg_replace('/\xc2\xa0/', ' ', strip_tags($request->input('description', ''))));
            $validator->after(function ($v) use ($plain) {
                if ($plain === '') {
                    $v->errors()->add('description', 'La descripción no puede estar vacía.');
                }
            });

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // -------- Permisos y reglas de negocio --------
            $canFullEdit = $this->userCan('edit_wound_history');

            // Regla append-only para description si NO tiene permiso
            $applyDescription = (string) $request->description;
            $appendBlocked    = false;

            if (!$canFullEdit) {
                $incoming = $this->normalizeForAppend($applyDescription);
                $current  = $this->normalizeForAppend($history->description);

                $isAppendOnly = ($current === '') ||
                    ($incoming !== '' && strncmp($incoming, $current, strlen($current)) === 0);

                if (!$isAppendOnly) {
                    // Bloquea y conserva la descripción actual
                    $applyDescription = (string) $history->description;
                    $appendBlocked    = true;

                    // (opcional) log de bloqueo
                    $this->logChange([
                        'logType'    => 'WoundHistory',
                        'table'      => 'wound_histories',
                        'primaryKey' => $history->id,
                        'changeType' => 'permission-append-only-block',
                        'fieldName'  => 'description',
                        'oldValue'   => null,
                        'newValue'   => 'blocked-by-append-rule',
                    ]);
                }
            }

            // `type_bite` solo persiste si subtipo = 10
            $subtypeId       = (int) $request->wound_subtype_id;
            $typeBiteToSave  = ($subtypeId === 10) ? ($request->type_bite ?? null) : null;

            DB::beginTransaction();

            // Snapshot previo para auditoría
            $old = $history->only([
                'wound_type_id',
                'grade_foot',
                'type_bite',
                'wound_subtype_id',
                'body_location_id',
                'body_sublocation_id',
                'wound_phase_id',
                'woundBeginDate',
                'woundHealthDate',
                'measurementDate',
                'valoracion',
                'MESI',
                'borde',
                'edema',
                'dolor',
                'duracion_dolor',
                'exudado_cantidad',
                'exudado_tipo',
                'olor',
                'piel_perilesional',
                'infeccion',
                'tipo_dolor',
                'visual_scale',
                'monofilamento',
                'blood_glucose',
                'ITB_izquierdo',
                'pulse_dorsal_izquierdo',
                'pulse_tibial_izquierdo',
                'pulse_popliteo_izquierdo',
                'ITB_derecho',
                'pulse_dorsal_derecho',
                'pulse_tibial_derecho',
                'pulse_popliteo_derecho',
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
                'description',
            ]);

            // Asignamos (usa parseDate y respeta applyDescription + type_bite)
            $history->fill([
                'wound_type_id'             => $request->wound_type_id,
                'grade_foot'                => $request->grade_foot,
                'type_bite'                 => $typeBiteToSave,
                'wound_subtype_id'          => $request->wound_subtype_id,
                'body_location_id'          => $request->body_location_id,
                'body_sublocation_id'       => $request->body_sublocation_id,
                'wound_phase_id'            => $request->wound_phase_id,
                'woundBeginDate'            => $this->parseDate($request->woundBeginDate),
                'woundHealthDate'           => $this->parseDate($request->woundHealthDate),
                'measurementDate'           => $this->parseDate($request->measurementDate),
                'valoracion'                => $request->valoracion, // solo si existe esa columna
                'MESI'                      => $request->MESI,       // si así guardas el valor MESI
                'borde'                     => $request->borde,
                'edema'                     => $request->edema,
                'dolor'                     => $request->dolor,
                'duracion_dolor'            => $request->duracion_dolor,
                'exudado_cantidad'          => $request->exudado_cantidad,
                'exudado_tipo'              => $request->exudado_tipo,
                'olor'                      => $request->olor,
                'piel_perilesional'         => $request->input('piel_perilesional'),
                'infeccion'                 => $request->input('infeccion'),
                'tipo_dolor'                => $request->tipo_dolor,
                'visual_scale'              => $request->visual_scale,
                'monofilamento'             => $request->monofilamento,
                'blood_glucose'             => $request->blood_glucose,
                'ITB_izquierdo'             => $request->ITB_izquierdo,
                'pulse_dorsal_izquierdo'    => $request->pulse_dorsal_izquierdo,
                'pulse_tibial_izquierdo'    => $request->pulse_tibial_izquierdo,
                'pulse_popliteo_izquierdo'  => $request->pulse_popliteo_izquierdo,
                'ITB_derecho'               => $request->ITB_derecho,
                'pulse_dorsal_derecho'      => $request->pulse_dorsal_derecho,
                'pulse_tibial_derecho'      => $request->pulse_tibial_derecho,
                'pulse_popliteo_derecho'    => $request->pulse_popliteo_derecho,
                'length'                    => $request->length,
                'width'                     => $request->width,
                'area'                      => $request->area,
                'depth'                     => $request->depth,
                'volume'                    => $request->volume,
                'tunneling'                 => $request->tunneling,
                'undermining'               => $request->undermining,
                'granulation_percent'       => $request->granulation_percent,
                'slough_percent'            => $request->slough_percent,
                'necrosis_percent'          => $request->necrosis_percent,
                'epithelialization_percent' => $request->epithelialization_percent,
                'description'               => $applyDescription,
            ]);

            // Diferencias reales antes de guardar
            $dirty = $history->getDirty();

            $history->save();

            // Auditoría por campo modificado
            foreach ($dirty as $field => $newVal) {
                $this->logChange([
                    'logType'      => 'WoundHistory',
                    'table'        => 'wound_histories',
                    'primaryKey'   => $history->id,
                    'secondaryKey' => $history->wound_id,
                    'changeType'   => 'update',
                    'fieldName'    => $field,
                    'oldValue'     => json_encode($old[$field] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'newValue'     => json_encode($history->{$field}, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            DB::commit();

            return response()->json([
                'message'        => 'Antecedente actualizado correctamente.',
                'history'        => $history,
                'append_blocked' => $appendBlocked, // para mostrar toast en el front si aplica
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Error al actualizar antecedente de la herida');
            Log::debug($e);
            Log::error($e);

            return response()->json([
                'message' => 'Error inesperado al actualizar el antecedente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
