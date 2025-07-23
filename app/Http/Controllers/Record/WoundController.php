<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\Patient;
use App\Models\Site;
use App\Models\State;
use App\Models\Wound;
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class WoundController extends Controller
{
    public function index(Request $request, $appointmentId, $healthRecordId)
    {
        try {
            $decryptAppointmentId = Crypt::decryptString($appointmentId);
            $decryptHealthRecordId = Crypt::decryptString($healthRecordId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $wounds = Wound::select([
            'wounds.*',
            'list_wound_types.name as wound_type',
            'list_wound_types.id as wound_type_id',
            'list_wound_subtypes.name as wound_subtype',
            'list_wound_subtypes.id as wound_subtype_id',
            'list_body_locations.name as body_location',
            'list_body_locations.id as body_location_id',
            'list_body_sublocations.name as body_sublocation',
            'list_body_sublocations.id as body_sublocation_id',
            'list_wound_phases.name as wound_phase',
            'list_wound_phases.id as wound_phase_id',
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wounds.wound_phase_id')
            ->where('wounds.appointment_id', $decryptAppointmentId)
            ->where('wounds.health_record_id', $decryptHealthRecordId)
            ->where('wounds.state', 1)
            ->get();

        foreach ($wounds as $wound) {
            $wound->wound_id = Crypt::encryptString($wound->id);
            $histories = DB::table('wound_histories')
                ->select([
                    'wound_histories.*',
                    'list_wound_types.name as wound_type_history',
                    'list_wound_subtypes.name as wound_subtype_history',
                    'list_body_locations.name as body_location_history',
                    'list_body_sublocations.name as body_sublocation_history',
                    'list_wound_phases.name as wound_phase_history',
                ])
                ->join('list_wound_types', 'list_wound_types.id', '=', 'wound_histories.wound_type_id')
                ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wound_histories.wound_subtype_id')
                ->join('list_body_locations', 'list_body_locations.id', '=', 'wound_histories.body_location_id')
                ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wound_histories.body_sublocation_id')
                ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wound_histories.wound_phase_id')
                ->where('wound_histories.wound_id', $wound->id)
                ->where('wound_histories.state', 1)
                ->get();

            $wound->histories = $histories;
        }

        // Obtener el paciente desde el expediente
        $healthRecord = HealthRecord::with('patient.userDetail')->findOrFail($decryptHealthRecordId);
        $patient = $healthRecord->patient;
        $fullName = $patient && $patient->userDetail
            ? "{$patient->userDetail->name} {$patient->userDetail->fatherLastName} {$patient->userDetail->motherLastName}"
            : 'Nombre no disponible';

        return Inertia::render('Wounds/Index', [
            'wounds' => $wounds,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'woundsPhase' => WoundPhase::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'grades' => [
                ['label' => '1', 'value' => 1],
                ['label' => '2', 'value' => 2],
                ['label' => '3', 'value' => 3],
            ],
            'appointmentId' => $decryptAppointmentId,
            'healthRecordId' => $decryptHealthRecordId,
            'patient' => [
                'id' => $patient->id ?? null,
                'full_name' => $fullName,
            ],
        ]);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'appointment_id'      => 'required|exists:appointments,id',
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

            // Crear la herida
            $wound = Wound::create([
                'appointment_id'      => $request->appointment_id,
                'health_record_id'    => $request->health_record_id,
                'wound_type_id'       => $request->wound_type_id,
                'grade_foot'          => $request->grade_foot,
                'wound_subtype_id'    => $request->wound_subtype_id,
                'wound_type_other'    => $request->wound_type_other,
                'body_location_id'    => $request->body_location_id,
                'body_sublocation_id' => $request->body_sublocation_id,
                'wound_phase_id'      => $request->wound_phase_id,
                'woundBeginDate'      => Carbon::parse($request->woundBeginDate)->format('Y-m-d'),
            ]);

            return response()->json([
                'message' => 'Herida creada exitosamente',
                'wound'   => $wound,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al guardar la herida',
                'error' => $e->getMessage(),
            ], 500);
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
    public function edit($woundId)
    {
         try {
            $decryptWoundId = Crypt::decryptString($woundId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        return Inertia::render('Wounds/Edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
