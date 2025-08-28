<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Method;
use App\Models\Treatment;
use App\Models\TreatmentMethod;
use App\Models\TreatmentSubmethod;
use DragonCode\Support\Facades\Helpers\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TreatmentController extends Controller
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

    public function update(Request $request)
    {
        $request->validate([
            'treatment_id'        => 'nullable|exists:treatments,id',
            'appointment_id'      => 'required|exists:appointments,id',
            'wound_id'            => 'required|exists:wounds,id',
            'description'         => 'required|string',
            'method_ids'          => 'required|array|min:1',
            'method_ids.*'        => 'exists:list_treatment_methods,id',
            'submethodsByMethod'  => 'required|array',
            'submethodsByMethod.*' => 'array|min:1',
            'mmhg' => 'nullable|numeric',
        ], [
            'appointment_id.required' => 'El ID de la consulta es obligatorio.',
            'description.required'    => 'La descripción es obligatoria.',
            'method_ids.required'     => 'Debe seleccionar al menos un método.',
            'submethodsByMethod.*.array' => 'Debe seleccionar al menos un submétodo por método.',
            'mmhg.numeric' => 'El campo mmhg debe ser numérico.',
        ]);

        foreach ($request->method_ids as $methodId) {
            if (empty($request->submethodsByMethod[$methodId]) || !is_array($request->submethodsByMethod[$methodId])) {
                return response()->json([
                    'message' => 'Cada método debe tener al menos un submétodo.',
                    'errors'  => [
                        "submethodsByMethod.$methodId" => ['Debe seleccionar al menos un submétodo para este método.']
                    ]
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            if ($request->filled('treatment_id')) {

                $treatment = Treatment::with(['methods', 'submethods'])->findOrFail($request->treatment_id);

                // Log de change en description (si cambió)
                if ((string)$treatment->description !== (string)$request->description) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatments',
                        'primaryKey' => $treatment->id,
                        'changeType' => 'update',
                        'fieldName'  => 'description',
                        'oldValue'   => $treatment->description,
                        'newValue'   => $request->description,
                    ]);
                }

                // Actualiza descripción
                $treatment->update([
                    'description' => $request->description,
                ]);

                // Capturamos métodos/submétodos ANTES de borrar para log
                $oldMethods = $treatment->methods->map(function ($m) {
                    return [
                        'id'                  => $m->id,
                        'treatment_id'        => $m->treatment_id,
                        'treatment_method_id' => $m->treatment_method_id,
                    ];
                })->values()->all();

                $oldSubmethods = $treatment->submethods->map(function ($s) {
                    return [
                        'id'                       => $s->id,
                        'treatment_id'             => $s->treatment_id,
                        'treatment_method_id'      => $s->treatment_method_id,
                        'treatment_submethod_id'   => $s->treatment_submethod_id,
                    ];
                })->values()->all();

                // Log bulk-destroy de relaciones previas
                if (!empty($oldMethods)) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatment_methods',
                        'primaryKey' => null,
                        'secondaryKey' => $treatment->id,
                        'changeType' => 'bulk-destroy',
                        'oldValue'   => json_encode($oldMethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'newValue'   => null,
                    ]);
                }
                if (!empty($oldSubmethods)) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatment_submethods',
                        'primaryKey' => null,
                        'secondaryKey' => $treatment->id,
                        'changeType' => 'bulk-destroy',
                        'oldValue'   => json_encode($oldSubmethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'newValue'   => null,
                    ]);
                }

                // Eliminar relaciones viejas
                $treatment->methods()->delete();
                $treatment->submethods()->delete();
            } else {

                $treatment = Treatment::create([
                    'wound_id'       => $request->wound_id,
                    'appointment_id' => $request->appointment_id,
                    'beginDate'      => now(),
                    'description'    => $request->description,
                    'mmhg'           => $request->mmhg,
                    'state'          => 1,
                ]);

                // Log de creación de tratamiento
                $this->logChange([
                    'logType'      => 'Tratamiento',
                    'table'        => 'treatments',
                    'primaryKey'   => $treatment->id,
                    'secondaryKey' => $request->wound_id,
                    'changeType'   => 'create',
                    'newValue'     => json_encode($treatment->only([
                        'id',
                        'wound_id',
                        'appointment_id',
                        'beginDate',
                        'description',
                        'state',
                        'created_at'
                    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
            // === NUEVO: Upsert por (treatment_id) o por (wound_id appointment_id) ===
            $treatment = null;
            $isUpdate  = false;

            if ($request->filled('treatment_id')) {
                // UPDATE directo por ID
                $treatment = Treatment::with(['methods', 'submethods'])
                    ->findOrFail($request->treatment_id);
                $isUpdate = true;
            } else {
                // Busca existente por (wound_id appointment_id)
                $treatment = Treatment::where('wound_id', $request->wound_id)
                    ->where('appointment_id', $request->appointment_id)
                    ->with(['methods', 'submethods'])
                    ->first();
                $isUpdate = (bool) $treatment;
            }

            if ($isUpdate) {
                // Log de cambio en descripción (si cambió)
                if ((string)$treatment->description !== (string)$request->description) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatments',
                        'primaryKey' => $treatment->id,
                        'changeType' => 'update',
                        'fieldName'  => 'description',
                        'oldValue'   => $treatment->description,
                        'newValue'   => $request->description,
                    ]);
                }

                // Actualiza descripción
                $treatment->update([
                    'description' => $request->description,
                ]);

                // Capturar relaciones previas para log
                $oldMethods = $treatment->methods->map(function ($m) {
                    return [
                        'id'                  => $m->id,
                        'treatment_id'        => $m->treatment_id,
                        'treatment_method_id' => $m->treatment_method_id,
                    ];
                })->values()->all();

                $oldSubmethods = $treatment->submethods->map(function ($s) {
                    return [
                        'id'                     => $s->id,
                        'treatment_id'           => $s->treatment_id,
                        'treatment_method_id'    => $s->treatment_method_id,
                        'treatment_submethod_id' => $s->treatment_submethod_id,
                    ];
                })->values()->all();

                // Logs bulk-destroy
                if (!empty($oldMethods)) {
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_methods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-destroy',
                        'oldValue'     => json_encode($oldMethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'newValue'     => null,
                    ]);
                }
                if (!empty($oldSubmethods)) {
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_submethods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-destroy',
                        'oldValue'     => json_encode($oldSubmethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'newValue'     => null,
                    ]);
                }

                // Borra relaciones previas
                $treatment->methods()->delete();
                $treatment->submethods()->delete();
            } else {
                // CREATE
                $treatment = Treatment::create([
                    'wound_id'       => $request->wound_id,
                    'appointment_id' => $request->appointment_id,
                    'beginDate'      => now(),
                    'description'    => $request->description,
                    'state'          => 1,
                ]);

                // Log de creación
                $this->logChange([
                    'logType'      => 'Tratamiento',
                    'table'        => 'treatments',
                    'primaryKey'   => $treatment->id,
                    'secondaryKey' => $request->wound_id,
                    'changeType'   => 'create',
                    'newValue'     => json_encode($treatment->only([
                        'id',
                        'wound_id',
                        'appointment_id',
                        'beginDate',
                        'description',
                        'state',
                        'created_at'
                    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            // ===== Guardar nuevos métodos y submétodos =====
            $newMethods = [];
            $newSubmethods = [];

            foreach ($request->method_ids as $methodId) {
                $tm = TreatmentMethod::create([
                    'treatment_id'        => $treatment->id,
                    'treatment_method_id' => $methodId,
                ]);

                $newMethods[] = [
                    'id'                  => $tm->id,
                    'treatment_id'        => $tm->treatment_id,
                    'treatment_method_id' => $tm->treatment_method_id,
                    'created_at'          => $tm->created_at?->toDateTimeString(),
                ];

                foreach ($request->submethodsByMethod[$methodId] as $subId) {
                    $ts = TreatmentSubmethod::create([
                        'treatment_id'           => $treatment->id,
                        'treatment_method_id'    => $methodId,
                        'treatment_submethod_id' => $subId,
                    ]);

                    $newSubmethods[] = [
                        'id'                       => $ts->id,
                        'treatment_id'             => $ts->treatment_id,
                        'treatment_method_id'      => $ts->treatment_method_id,
                        'treatment_submethod_id'   => $ts->treatment_submethod_id,
                        'created_at'               => $ts->created_at?->toDateTimeString(),
                    ];
                }
            }

            // Logs bulk-create de nuevas relaciones
            $this->logChange([
                'logType'      => 'Tratamiento',
                'table'        => 'treatment_methods',
                'primaryKey'   => null,
                'secondaryKey' => $treatment->id,
                'changeType'   => 'bulk-create',
                'newValue'     => json_encode($newMethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $this->logChange([
                'logType'      => 'Tratamiento',
                'table'        => 'treatment_submethods',
                'primaryKey'   => null,
                'secondaryKey' => $treatment->id,
                'changeType'   => 'bulk-create',
                'newValue'     => json_encode($newSubmethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            DB::commit();

            return response()->json([
                'message' => $request->filled('treatment_id')
                    ? 'Tratamiento actualizado correctamente.'
                    : 'Tratamiento registrado correctamente.',
                'treatment_id' => $treatment->id,
            ]);
            return response()->json([
                'message'      => $isUpdate ? 'Tratamiento actualizado correctamente.' : 'Tratamiento registrado correctamente.',
                'treatment_id' => $treatment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar tratamiento: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Error al guardar el tratamiento.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'treatment_id'          => 'nullable|exists:treatments,id',
                'appointment_id'        => 'required|exists:appointments,id',
                'wound_id'              => 'required|exists:wounds,id',
                'description'           => 'required|string|min:1',
                'method_ids'            => 'required|array|min:1',
                'method_ids.*'          => 'exists:list_treatment_methods,id',
                'submethodsByMethod'    => 'required|array',
                'submethodsByMethod.*'  => 'array|min:1',
                'mmhg'                  => 'nullable|numeric',
            ],
            [
                'appointment_id.required'    => 'El ID de la consulta es obligatorio.',
                'description.required'       => 'La descripción es obligatoria.',
                'method_ids.required'        => 'Debe seleccionar al menos un método.',
                'submethodsByMethod.*.array' => 'Debe seleccionar al menos un submétodo por método.',
                'mmhg.numeric'               => 'El campo mmhg debe ser numérico.',
            ]
        );

        // Evitar descripción vacía con solo etiquetas
        $plain = trim(preg_replace('/\xc2\xa0/', ' ', strip_tags($request->input('description'))));
        $validator->after(function ($v) use ($plain) {
            if ($plain === '') {
                $v->errors()->add('description', 'La descripción no puede estar vacía.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Permiso: ¿puede reemplazar completamente la descripción?
        $canFullEdit = $this->userCan('edit_treatment');

        // Helper inline para empaquetar SOLO los datos del request en logs
        $requestPayload = static function (Request $r): array {
            $methodIds = array_map('intval', (array) $r->input('method_ids', []));
            $subsByMethod = [];
            foreach ((array) $r->input('submethodsByMethod', []) as $mId => $subs) {
                $subsByMethod[(int)$mId] = array_map('intval', (array) $subs);
            }
            return [
                'treatment_id'        => $r->input('treatment_id'),
                'appointment_id'      => $r->input('appointment_id'),
                'wound_id'            => $r->input('wound_id'),
                'description'         => (string) $r->input('description'),
                'mmhg'                => $r->input('mmhg'),
                'method_ids'          => $methodIds,
                'submethodsByMethod'  => $subsByMethod,
            ];
        };

        // Normalizador inline (sin helpers/Str): strip_tags, colapsa espacios, lower
        $normalize = static function (?string $html): string {
            $txt = strip_tags((string) $html);
            $txt = preg_replace('/\xc2\xa0/', ' ', $txt);      // nbsp
            $txt = preg_replace('/\s+/u', ' ', trim($txt));    // colapsar espacios
            return mb_strtolower($txt, 'UTF-8');
        };

        DB::beginTransaction();
        try {
            if ($request->filled('treatment_id')) {
                // ====== UPDATE ======
                $treatment = Treatment::with(['methods', 'submethods'])
                    ->findOrFail($request->treatment_id);

                // Regla append-only en description si NO tiene permiso
                $applyDescription = (string) $request->description; // por defecto lo del front
                $appendBlocked = false;

                if (!$canFullEdit) {
                    $incomingClean = $normalize($request->description);
                    $currentClean  = $normalize($treatment->description);

                    $isAppendOnly = ($currentClean === '') ||
                        ($incomingClean !== '' && strncmp($incomingClean, $currentClean, strlen($currentClean)) === 0);

                    if (!$isAppendOnly) {
                        // Log del bloqueo (SOLO request)
                        $this->logChange([
                            'logType'    => 'Tratamiento',
                            'table'      => 'treatments',
                            'primaryKey' => $treatment->id,
                            'changeType' => 'permission-append-only-block',
                            'fieldName'  => 'description',
                            'oldValue'   => null,
                            'newValue'   => json_encode($requestPayload($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                        // Mantener descripción actual
                        $applyDescription = (string) $treatment->description;
                        $appendBlocked = true;
                    }
                }

                // Detectar cambios
                $descChanged = ((string) $treatment->description !== (string) $applyDescription);

                $currentMethodIds = $treatment->methods
                    ->pluck('treatment_method_id')
                    ->map(fn($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                $requestMethodIds = collect($request->method_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                $methodsChanged = ($currentMethodIds !== $requestMethodIds);

                $currentSubsByMethod = $treatment->submethods
                    ->groupBy('treatment_method_id')
                    ->map(function ($group) {
                        return $group->pluck('treatment_submethod_id')
                            ->map(fn($id) => (int) $id)
                            ->sort()
                            ->values()
                            ->all();
                    })
                    ->toArray();

                $requestSubsByMethod = [];
                foreach ($requestMethodIds as $mId) {
                    $subs = Arr::get($request->submethodsByMethod, $mId, []);
                    $requestSubsByMethod[(int) $mId] = collect($subs)
                        ->map(fn($id) => (int) $id)
                        ->sort()
                        ->values()
                        ->all();
                }

                ksort($currentSubsByMethod);
                ksort($requestSubsByMethod);

                $submethodsChanged = ($currentSubsByMethod !== $requestSubsByMethod);

                $mmhgChanged = $request->has('mmhg') && ((string) $treatment->mmhg !== (string) $request->mmhg);

                if (!$descChanged && !$methodsChanged && !$submethodsChanged && !$mmhgChanged) {
                    DB::rollBack();
                    return response()->json([
                        'message'        => 'Tratamiento actualizado correctamente.',
                        'treatment_id'   => $treatment->id,
                        'append_blocked' => $appendBlocked,
                    ]);
                }

                // Aplicar cambios y loguear SOLO request
                if ($descChanged) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatments',
                        'primaryKey' => $treatment->id,
                        'changeType' => 'update',
                        'fieldName'  => 'description',
                        'oldValue'   => null,
                        'newValue'   => json_encode($requestPayload($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                    $treatment->description = $applyDescription;
                }

                if ($mmhgChanged) {
                    $this->logChange([
                        'logType'    => 'Tratamiento',
                        'table'      => 'treatments',
                        'primaryKey' => $treatment->id,
                        'changeType' => 'update',
                        'fieldName'  => 'mmhg',
                        'oldValue'   => null,
                        'newValue'   => json_encode(['mmhg' => $request->mmhg], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                    $treatment->mmhg = $request->mmhg; // puede ser null
                }

                $treatment->save();

                // Rehacer relaciones si cambiaron
                if ($methodsChanged || $submethodsChanged) {
                    // Logs de "destroy" SOLO con request (como pediste)
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_methods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-destroy',
                        'oldValue'     => null,
                        'newValue'     => json_encode(['method_ids' => $requestMethodIds], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_submethods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-destroy',
                        'oldValue'     => null,
                        'newValue'     => json_encode(['submethodsByMethod' => $requestSubsByMethod], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);

                    $treatment->methods()->delete();
                    $treatment->submethods()->delete();

                    $newMethods = [];
                    $newSubmethods = [];

                    foreach ($requestMethodIds as $methodId) {
                        $tm = TreatmentMethod::create([
                            'treatment_id'        => $treatment->id,
                            'treatment_method_id' => $methodId,
                        ]);
                        $newMethods[] = [
                            'id'                  => $tm->id,
                            'treatment_id'        => $tm->treatment_id,
                            'treatment_method_id' => $tm->treatment_method_id,
                            'created_at'          => $tm->created_at?->toDateTimeString(),
                        ];

                        foreach ($requestSubsByMethod[$methodId] as $subId) {
                            $ts = TreatmentSubmethod::create([
                                'treatment_id'           => $treatment->id,
                                'treatment_method_id'    => $methodId,
                                'treatment_submethod_id' => $subId,
                            ]);
                            $newSubmethods[] = [
                                'id'                       => $ts->id,
                                'treatment_id'             => $ts->treatment_id,
                                'treatment_method_id'      => $ts->treatment_method_id,
                                'treatment_submethod_id'   => $ts->treatment_submethod_id,
                                'created_at'               => $ts->created_at?->toDateTimeString(),
                            ];
                        }
                    }

                    // Logs de "create" con request
                    if (!empty($newMethods)) {
                        $this->logChange([
                            'logType'      => 'Tratamiento',
                            'table'        => 'treatment_methods',
                            'primaryKey'   => null,
                            'secondaryKey' => $treatment->id,
                            'changeType'   => 'bulk-create',
                            'newValue'     => json_encode($newMethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                    if (!empty($newSubmethods)) {
                        $this->logChange([
                            'logType'      => 'Tratamiento',
                            'table'        => 'treatment_submethods',
                            'primaryKey'   => null,
                            'secondaryKey' => $treatment->id,
                            'changeType'   => 'bulk-create',
                            'newValue'     => json_encode($newSubmethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message'        => 'Tratamiento actualizado correctamente.',
                    'treatment_id'   => $treatment->id,
                    'append_blocked' => $appendBlocked,
                ]);
            } else {
                // ====== CREATE ======
                $treatment = Treatment::create([
                    'wound_id'       => $request->wound_id,
                    'appointment_id' => $request->appointment_id,
                    'beginDate'      => now(),
                    'description'    => $request->description,
                    'mmhg'           => $request->mmhg,
                    'state'          => 1,
                ]);

                // Log de creación: SOLO request
                $this->logChange([
                    'logType'      => 'Tratamiento',
                    'table'        => 'treatments',
                    'primaryKey'   => $treatment->id,
                    'secondaryKey' => $request->wound_id,
                    'changeType'   => 'create',
                    'newValue'     => json_encode($requestPayload($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                // Crear relaciones
                $newMethods = [];
                $newSubmethods = [];

                $requestMethodIds = collect($request->method_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                foreach ($requestMethodIds as $methodId) {
                    $tm = TreatmentMethod::create([
                        'treatment_id'        => $treatment->id,
                        'treatment_method_id' => $methodId,
                    ]);
                    $newMethods[] = [
                        'id'                  => $tm->id,
                        'treatment_id'        => $tm->treatment_id,
                        'treatment_method_id' => $tm->treatment_method_id,
                        'created_at'          => $tm->created_at?->toDateTimeString(),
                    ];

                    $subs = Arr::get($request->submethodsByMethod, $methodId, []);
                    foreach ($subs as $subId) {
                        $ts = TreatmentSubmethod::create([
                            'treatment_id'           => $treatment->id,
                            'treatment_method_id'    => $methodId,
                            'treatment_submethod_id' => (int) $subId,
                        ]);
                        $newSubmethods[] = [
                            'id'                       => $ts->id,
                            'treatment_id'             => $ts->treatment_id,
                            'treatment_method_id'      => $ts->treatment_method_id,
                            'treatment_submethod_id'   => $ts->treatment_submethod_id,
                            'created_at'               => $ts->created_at?->toDateTimeString(),
                        ];
                    }
                }

                if (!empty($newMethods)) {
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_methods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-create',
                        'newValue'     => json_encode($newMethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                }
                if (!empty($newSubmethods)) {
                    $this->logChange([
                        'logType'      => 'Tratamiento',
                        'table'        => 'treatment_submethods',
                        'primaryKey'   => null,
                        'secondaryKey' => $treatment->id,
                        'changeType'   => 'bulk-create',
                        'newValue'     => json_encode($newSubmethods, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message'      => 'Tratamiento registrado correctamente.',
                    'treatment_id' => $treatment->id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar tratamiento: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Error al guardar el tratamiento.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getTreatmentMethodsWithSubmethods()
    {
        $methods = Method::with(['submethods' => function ($q) {
            $q->where('state', true);
        }])
            ->where('state', true)
            ->get();

        return response()->json($methods);
    }
}
