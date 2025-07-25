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
            'wound_id'          => 'required',
        ]);

        DB::beginTransaction();

        try {
            $date = Carbon::parse($request->measurementDate)->format('Y-m-d');

            // Buscar si ya existe una medición para esa herida y fecha
            $measurement = Measurement::where('wound_id', $request->wound_id)
                ->whereDate('measurementDate', $date)
                ->first();

            $data = [
                'wound_id'          => $request->wound_id,
                'appointment_id'    => $request->appointment_id,
                'measurementDate'   => $date,
                'lenght'            => $request->lenght,
                'width'             => $request->width,
                'area'              => $request->area,
                'maxDepth'          => $request->maxDepth,
                'avgDepth'          => $request->avgDepth,
                'volume'            => $request->volume,
                'redPercentaje'     => $request->redPercentaje,
                'yellowPercentaje'  => $request->yellowPercentaje,
                'blackPercentaje'   => $request->blackPercentaje,
            ];

            if ($measurement) {
                $measurement->update($data);
            } else {
                $measurement = Measurement::create($data);
            }

            DB::commit();

            return response()->json([
                'message' => $measurement->wasRecentlyCreated
                    ? 'Medición creada correctamente.'
                    : 'Medición actualizada correctamente.',
                'measurement' => $measurement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al guardar medición', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Error al guardar la medición.'], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
