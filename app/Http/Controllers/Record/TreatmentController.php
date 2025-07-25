<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Method;
use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            // otros campos de tratamiento
            'method_ids' => 'array',
            'method_ids.*' => 'exists:list_treatment_methods,id',
            'submethod_ids' => 'array',
            'submethod_ids.*' => 'exists:list_treatment_submethods,id',
        ]);

        $treatment = Treatment::create([
            'patient_id' => $validated['patient_id'],
            // otros campos
        ]);

        if (!empty($validated['method_ids'])) {
            $treatment->treatmentMethods()->sync($validated['method_ids']);
        }

        if (!empty($validated['submethod_ids'])) {
            $treatment->treatmentSubmethods()->sync($validated['submethod_ids']);
        }

        return response()->json([
            'message' => 'Tratamiento guardado correctamente.',
            'treatment' => $treatment
        ]);
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
