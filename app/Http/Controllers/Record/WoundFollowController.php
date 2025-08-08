<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\Measurement;
use App\Models\Media;
use App\Models\Method;
use App\Models\Submethod;
use App\Models\Treatment;
use App\Models\Wound;
use App\Models\WoundFollow;
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WoundFollowController extends Controller
{

    public function edit($appointmentId, $woundId)
    {
        try {
            $decryptWoundId = Crypt::decryptString($woundId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }


        $wound = Wound::select([
            'wounds.*',
            'list_wound_types.name as wound_type_name',
            'list_wound_types.id as wound_type_id',
            'list_wound_subtypes.name as wound_subtype_name',
            'list_wound_subtypes.id as wound_subtype_id',
            'list_body_locations.name as body_location_name',
            'list_body_locations.id as body_location_id',
            'list_body_sublocations.name as body_sublocation_name',
            'list_body_sublocations.id as body_sublocation_id',
            'list_wound_phases.name as wound_phase_name',
            'list_wound_phases.id as wound_phase_id',
        ])
            ->join('list_wound_types', 'list_wound_types.id', '=', 'wounds.wound_type_id')
            ->join('list_wound_subtypes', 'list_wound_subtypes.id', '=', 'wounds.wound_subtype_id')
            ->join('list_body_locations', 'list_body_locations.id', '=', 'wounds.body_location_id')
            ->join('list_body_sublocations', 'list_body_sublocations.id', '=', 'wounds.body_sublocation_id')
            ->join('list_wound_phases', 'list_wound_phases.id', '=', 'wounds.wound_phase_id')
            ->where('wounds.id', $decryptWoundId)
            ->firstOrFail();

        $measurement = Measurement::where('wound_id', $decryptWoundId)->first();

        $mediaHistory = Media::where('wound_id', $decryptWoundId)
            ->get(['id', 'content', 'position']);

        $mediaFollow = Media::where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->get(['id', 'content', 'position']);

        $treatmentsHistory = Treatment::with([
            'methods:id,treatment_id,treatment_method_id',
            'submethods:id,treatment_id,treatment_submethod_id,treatment_method_id'
        ])
            ->where('wound_id', $decryptWoundId)
            ->get();

        $treatmentFollow = Treatment::with([
            'methods:id,treatment_id,treatment_method_id',
            'submethods:id,treatment_id,treatment_submethod_id,treatment_method_id'
        ])
            ->where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->first();

        $follow = WoundFollow::where('wound_id', $decryptWoundId)
            ->where('appointment_id', $appointmentId)
            ->first();

        return Inertia::render('WoundsFollow/Edit', [
            'woundId' => $woundId,
            'appointmentId' => $appointmentId,
            'wound' => $wound,
            'follow' => $follow,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'woundsPhase' =>    WoundPhase::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'treatmentFollow' => $treatmentFollow,
            'measurement' => $measurement,
            'existingImagesHistory' => $mediaHistory,
            'existingImagesFollow' => $mediaFollow,
            'treatmentMethods' => Method::where('state', 1)
                ->with(['submethods' => function ($q) {
                    $q->where('state', 1);
                }])
                ->get(),

            'treatmentSubmethods' => Submethod::where('state', 1)->get(),
            'treatmentsHistory' => $treatmentsHistory,
        ]);
    }

    public function update(Request $request, $woundId)
    {
        try {
            $decryptedWoundId = Crypt::decryptString($woundId);
            $request->merge(['wound_id' => $decryptedWoundId]);

            $validated = $request->validate([
                'wound_id' => 'required|exists:wounds,id',
                'appointment_id' => 'required|exists:appointments,id',
                'wound_phase_id' => 'required|exists:list_wound_phases,id',
                'wound_type_id' => 'required|exists:list_wound_types,id',
                'wound_subtype_id' => 'required|exists:list_wound_subtypes,id',
                'body_location_id' => 'required|exists:list_body_locations,id',
                'body_sublocation_id' => 'required|exists:list_body_sublocations,id',
                'grade_foot' => 'nullable|string|max:255',
                'valoracion' => 'nullable|string|max:255',
                'MESI' => 'nullable|string|max:255',
                'borde' => 'nullable|string|max:255',
                'edema' => 'nullable|string|max:255',
                'dolor' => 'nullable|string|max:255',
                'exudado_cantidad' => 'nullable|string|max:255',
                'exudado_tipo' => 'nullable|string|max:255',
                'olor' => 'nullable|string|max:255',
                'piel_perilesional' => 'nullable|array',
                'infeccion' => 'nullable|array',
                'tipo_dolor' => 'nullable|string|max:255',
                'visual_scale' => 'nullable|string|max:255',
                'ITB_izquierdo' => 'nullable|string|max:255',
                'pulse_dorsal_izquierdo' => 'nullable|string|max:255',
                'pulse_tibial_izquierdo' => 'nullable|string|max:255',
                'pulse_popliteo_izquierdo' => 'nullable|string|max:255',
                'ITB_derecho' => 'nullable|string|max:255',
                'pulse_dorsal_derecho' => 'nullable|string|max:255',
                'pulse_tibial_derecho' => 'nullable|string|max:255',
                'pulse_popliteo_derecho' => 'nullable|string|max:255',
                'monofilamento' => 'nullable|string|max:255',
                'blood_glucose' => 'nullable|string|max:255',
                'measurementDate' => 'required|date',
                'length' => 'nullable|numeric',
                'width' => 'nullable|numeric',
                'area' => 'nullable|numeric',
                'depth' => 'nullable|numeric',
                'volume' => 'nullable|numeric',
                'tunneling' => 'nullable|string|max:255',
                'undermining' => 'nullable|string|max:255',
                'granulation_percent' => 'nullable|numeric',
                'slough_percent' => 'nullable|numeric',
                'necrosis_percent' => 'nullable|numeric',
                'epithelialization_percent' => 'nullable|numeric',
                'note' => 'nullable|string',
            ]);

            $follow = WoundFollow::firstOrNew([
                'wound_id' => $validated['wound_id'],
                'appointment_id' => $validated['appointment_id'],
            ]);

            $follow->fill($validated);
            $follow->piel_perilesional = json_encode($request->piel_perilesional ?? []);
            $follow->infeccion = json_encode($request->infeccion ?? []);
            $follow->save();

            return redirect()->back()->with('success', $follow->wasRecentlyCreated
                ? 'Seguimiento creado correctamente.'
                : 'Seguimiento actualizado correctamente.');
        } catch (DecryptException $e) {
            Log::error('Error al desencriptar el woundId', [
                'wound_id_raw' => $woundId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'ID de herida inválido o manipulado.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error general al guardar seguimiento', [
                'wound_id' => $woundId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al guardar el seguimiento.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
