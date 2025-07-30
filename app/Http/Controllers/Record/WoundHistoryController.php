<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\WoundHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundHistoryController extends Controller
{
    public function store(Request $request)
    {
        try {
            $rules = [
                'health_record_id'    => 'required|exists:health_records,id',
                'wound_type_id'       => 'required|exists:list_wound_types,id',
                'grade_foot'          => 'nullable|integer',
                'wound_subtype_id'    => 'required|exists:list_wound_subtypes,id',
                'wound_type_other'    => 'nullable|string|max:255',
                'body_location_id'    => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'wound_phase_id'      => 'required|exists:list_wound_phases,id',
                'woundBeginDate'      => 'required|date',
            ];

            $validator = Validator::make($request->all(), $rules);

            // Validación condicional: si el tipo de herida es 8, grade_foot es obligatorio
            $validator->sometimes('grade_foot', 'required', function ($input) {
                return $input->wound_type_id == 8;
            });

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $history = WoundHistory::create([
                'wound_id'            => $request->wound_id,
                'wound_type_id'       => $request->wound_type_id,
                'grade_foot'          => $request->grade_foot,
                'wound_subtype_id'    => $request->wound_subtype_id,
                'wound_type_other'    => $request->wound_type_other,
                'body_location_id'    => $request->body_location_id,
                'body_sublocation_id' => $request->body_sublocation_id,
                'wound_phase_id'      => $request->wound_phase_id,
                'woundBeginDate'      => Carbon::parse($request->woundBeginDate)->format('Y-m-d'),
                'woundBackground'     => 1,
            ]);

            return response()->json([
                'message' => 'Antecedente de herida guardado correctamente',
                'history' => $history,
            ]);
        } catch (\Exception $e) {
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

        dd($decryptHistoryId);

        return Inertia::render('WoundsHis/Edit');
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
