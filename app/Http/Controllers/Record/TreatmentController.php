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
            'note' => 'required|string',
            'method_ids' => 'required|array|min:1',
            'method_ids.*' => 'exists:list_treatment_methods,id',
            'submethod_ids' => 'nullable|array',
            'submethod_ids.*' => 'exists:list_treatment_submethods,id',
        ]);

        try {
            DB::beginTransaction();

            // Verifica si ya existe tratamiento
            $treatment = Treatment::where('wound_id', $request->wound_id)->first();

            if (!$treatment) {
                // Si no existe, lo crea
                $treatment = Treatment::create([
                    'wound_id' => $request->wound_id,
                    'description' => $request->note,
                    'beginDate' => now(),
                    'state' => 1,
                ]);
            } else {
                // Si existe, lo actualiza
                $treatment->update([
                    'description' => $request->note,
                    'state' => 1,
                ]);

                // Limpia métodos y submétodos previos
                $treatment->methods()->delete();
                $treatment->submethods()->delete();
            }

            // Registra métodos
            foreach ($request->method_ids as $methodId) {
                TreatmentMethod::create([
                    'treatment_id' => $treatment->id,
                    'treatment_method_id' => $methodId,
                ]);
            }

            // Registra submétodos
            foreach ($request->submethod_ids ?? [] as $submethodId) {
                TreatmentSubmethod::create([
                    'treatment_id' => $treatment->id,
                    'treatment_submethod_id' => $submethodId,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Tratamiento guardado/actualizado correctamente.'], 200);
        } catch (\Exception $e) {
            Log::info('Crear Tratamiento');
            Log::debug($e);
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'message' => 'Error al guardar tratamiento',
                'error' => $e->getMessage()
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
