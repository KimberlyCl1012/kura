<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Method;
use App\Models\Treatment;
use App\Models\TreatmentMethod;
use App\Models\TreatmentSubmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreatmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'treatment_id' => 'nullable|exists:treatments,id',
            'appointment_id' => 'required|exists:appointments,id',
            'wound_id' => 'required|exists:wounds,id',
            'description' => 'required|string',
            'method_ids' => 'required|array|min:1',
            'method_ids.*' => 'exists:list_treatment_methods,id',
            'submethodsByMethod' => 'required|array',
            'submethodsByMethod.*' => 'array|min:1',
        ], [
            'appointment_id.required' => 'El ID de la consulta es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'method_ids.required' => 'Debe seleccionar al menos un método.',
            'submethodsByMethod.*.array' => 'Debe seleccionar al menos un submétodo por método.',
        ]);

        // Validar que cada método tenga al menos un submétodo asociado
        foreach ($request->method_ids as $methodId) {
            if (empty($request->submethodsByMethod[$methodId]) || !is_array($request->submethodsByMethod[$methodId])) {
                return response()->json([
                    'message' => 'Cada método debe tener al menos un submétodo.',
                    'errors' => [
                        "submethodsByMethod.$methodId" => ['Debe seleccionar al menos un submétodo para este método.']
                    ]
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            if ($request->filled('treatment_id')) {
                // Actualizar tratamiento existente
                $treatment = Treatment::findOrFail($request->treatment_id);
                $treatment->update([
                    'description' => $request->description,
                ]);

                // Eliminar métodos y submétodos anteriores
                $treatment->methods()->delete();
                $treatment->submethods()->delete();
            } else {
                // Crear nuevo tratamiento
                $treatment = Treatment::create([
                    'wound_id' => $request->wound_id,
                    'appointment_id' => $request->appointment_id,
                    'beginDate' => now(),
                    'description' => $request->description,
                    'state' => 1,
                ]);
            }

            // Guardar nuevos métodos y submétodos
            foreach ($request->method_ids as $methodId) {
                TreatmentMethod::create([
                    'treatment_id' => $treatment->id,
                    'treatment_method_id' => $methodId,
                ]);

                foreach ($request->submethodsByMethod[$methodId] as $subId) {
                    TreatmentSubmethod::create([
                        'treatment_id' => $treatment->id,
                        'treatment_method_id' => $methodId,
                        'treatment_submethod_id' => $subId,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => $request->filled('treatment_id')
                    ? 'Tratamiento actualizado correctamente.'
                    : 'Tratamiento registrado correctamente.',
                'treatment_id' => $treatment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar tratamiento: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error al guardar el tratamiento.',
                'error' => $e->getMessage(),
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
