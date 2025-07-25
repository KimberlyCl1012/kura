<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeasurementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wound_id' => 'required|integer|exists:wounds,id',
            'measurementDate' => 'required|date',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
            // Profundidad puede ser null, pero si se envía debe ser numérico
            'depth' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'tunneling' => 'required|string|max:255',
            'undermining' => 'required|string|max:255',
            'granulation_percent' => 'required|numeric|min:0|max:100',
            'slough_percent' => 'required|numeric|min:0|max:100',
            'necrosis_percent' => 'required|numeric|min:0|max:100',
            'epithelialization_percent' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $date = Carbon::parse($request->measurementDate)->format('Y-m-d');
            // Buscar si ya existe una medición para esa herida y fecha
            $measurement = Measurement::where('wound_id', $request->wound_id)
                ->whereDate('measurementDate', $date)
                ->first();

            $data = [
                'wound_id' => $request->wound_id,
                'appointment_id' => $request->appointmentId,
                'measurementDate' => $date,
                'length' => $request->length,
                'width' => $request->width,
                'area' => $request->area,
                'depth' => $request->depth,
                'volume' => $request->volume,
                'tunneling' => $request->tunneling,
                'undermining' => $request->undermining,
                'granulation_percent' => $request->granulation_percent,
                'slough_percent' => $request->slough_percent,
                'necrosis_percent' => $request->necrosis_percent,
                'epithelialization_percent' => $request->epithelialization_percent,
            ];

            if ($measurement) {
                $measurement->update($data);
                $message = 'Medición actualizada correctamente.';
            } else {
                $measurement = Measurement::create($data);
                $message = 'Medición creada correctamente.';
            }

            return response()->json([
                'message' => $message,
                'measurement' => $measurement
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar medición', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Error al guardar la medición.'], 500);
        }
    }
}
