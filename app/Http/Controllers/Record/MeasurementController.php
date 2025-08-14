<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeasurementController extends Controller
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wound_id' => 'required|integer|exists:wounds,id',
            'appointment_id' => 'required|integer|exists:appointments,id',
            'measurementDate' => 'required|date',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
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
            $date = Carbon::parse($validated['measurementDate'])->format('Y-m-d');

            $data = [
                'wound_id' => $validated['wound_id'],
                'appointment_id' => $validated['appointment_id'],
                'measurementDate' => $date,
                'length' => $validated['length'],
                'width' => $validated['width'],
                'area' => $validated['area'],
                'depth' => $validated['depth'] ?? null,
                'volume' => $validated['volume'] ?? null,
                'tunneling' => $validated['tunneling'] ?? null,
                'undermining' => $validated['undermining'],
                'granulation_percent' => $validated['granulation_percent'],
                'slough_percent' => $validated['slough_percent'],
                'necrosis_percent' => $validated['necrosis_percent'],
                'epithelialization_percent' => $validated['epithelialization_percent'],
            ];

            DB::beginTransaction();

            // Buscar si ya existe una medición para esa herida y fecha
            $measurement = Measurement::where('wound_id', $validated['wound_id'])
                ->whereDate('measurementDate', $date)
                ->first();

            if ($measurement) {
                $before = $measurement->replicate();

                $measurement->update($data);

                // Log campo por campo
                $fields = [
                    'appointment_id',
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
                ];

                foreach ($fields as $field) {
                    $old = (string)($before->$field ?? '');
                    $new = (string)($measurement->$field ?? '');
                    if ($old !== $new) {
                        $this->logChange([
                            'logType'    => 'Medición',
                            'table'      => 'measurements',
                            'primaryKey' => $measurement->id,
                            'secondaryKey' => $measurement->wound_id,
                            'changeType' => 'update',
                            'fieldName'  => $field,
                            'oldValue'   => $old,
                            'newValue'   => $new,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Medición actualizada correctamente.',
                    'measurement' => $measurement
                ]);
            } else {
                $measurement = Measurement::create($data);

                // Log de creación
                $this->logChange([
                    'logType'      => 'Medición',
                    'table'        => 'measurements',
                    'primaryKey'   => $measurement->id,
                    'secondaryKey' => $measurement->wound_id,
                    'changeType'   => 'create',
                    'newValue'     => json_encode($measurement->only([
                        'wound_id',
                        'appointment_id',
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
                        'created_at',
                    ])),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Medición creada correctamente.',
                    'measurement' => $measurement
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Guardar medición');
            Log::error('Error al guardar medición', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Error al guardar la medición.'], 500);
        }
    }
}
