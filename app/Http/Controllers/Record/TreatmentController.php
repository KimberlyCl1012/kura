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
            'wound_id' => 'required|exists:wounds,id',
            'description' => 'required|string',
            'method_ids' => 'required|array|min:1',
            'method_ids.*' => 'exists:list_treatment_methods,id',
            'submethodsByMethod' => 'required|array',
            'submethodsByMethod.*' => 'array',
        ], [
            'description.required' => 'La descripción es obligatoria.',
            'method_ids.required' => 'Debe seleccionar al menos un método.',
            'submethodsByMethod.*.array' => 'Debe seleccionar al menos un submétodo por método.',
        ]);

        foreach ($request->method_ids as $methodId) {
            if (empty($request->submethodsByMethod[$methodId]) || !is_array($request->submethodsByMethod[$methodId])) {
                return response()->json([
                    'message' => 'Cada método seleccionado debe tener al menos un submétodo.',
                    'errors' => [
                        "submethodsByMethod.$methodId" => ['Debe seleccionar al menos un submétodo para este método.']
                    ]
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $treatment = Treatment::firstOrCreate(
                ['wound_id' => $request->wound_id],
                ['beginDate' => now(), 'state' => 1]
            );

            $treatment->update(['description' => $request->description]);

            $treatment->methods()->delete();
            $treatment->submethods()->delete();

            foreach ($request->method_ids as $methodId) {
                TreatmentMethod::create([
                    'treatment_id' => $treatment->id,
                    'treatment_method_id' => $methodId,
                ]);

                $submethods = $request->submethodsByMethod[$methodId] ?? [];
                foreach ($submethods as $subId) {
                    TreatmentSubmethod::create([
                        'treatment_id' => $treatment->id,
                        'treatment_method_id' => $methodId,
                        'treatment_submethod_id' => $subId,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Tratamiento guardado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'message' => 'Error al guardar tratamiento',
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
