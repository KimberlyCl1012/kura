<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\BodyLocation;
use App\Models\BodySublocation;
use App\Models\Measurement;
use App\Models\Media;
use App\Models\Method;
use App\Models\Submethod;
use App\Models\Wound;
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class WoundFollowController extends Controller
{

    public function edit($woundId)
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

        $media = Media::where('wound_id', $decryptWoundId)
            ->where('type', 'Herida')
            ->get(['id', 'content', 'position']);

        return Inertia::render('WoundsFollow/Edit', [
            'wound' => $wound,
            'woundsType' => WoundType::where('state', 1)->get(),
            'woundsSubtype' => WoundSubtype::where('state', 1)->get(),
            'woundsPhase' =>    WoundPhase::where('state', 1)->get(),
            'bodyLocations' => BodyLocation::where('state', 1)->get(),
            'bodySublocation' => BodySublocation::where('state', 1)->get(),
            'measurement' => $measurement,
            'existingImages' => $media,
            'treatmentMethods' => Method::where('state', 1)
                ->with(['submethods' => function ($q) {
                    $q->where('state', 1);
                }])
                ->get(),

            'treatmentSubmethods' => Submethod::where('state', 1)->get(),
        ]);
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
