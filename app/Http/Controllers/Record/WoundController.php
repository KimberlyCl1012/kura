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
use App\Models\WoundPhase;
use App\Models\WoundSubtype;
use App\Models\WoundType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener pacientes con su nombre completo
        $patients = Patient::with('userDetail')->get()->map(function ($p) {
            $d = $p->userDetail;
            return [
                'id' => $p->id,
                'full_name' => "{$p->user_uuid} - {$d->name} {$d->fatherLastName} {$d->motherLastName}",
            ];
        });

        // Catálogos sin transformar (objetos completos)
        $woundsType        = WoundType::where('state', 1)->get();
        $woundsSubtype     = WoundSubtype::where('state', 1)->get();
        $woundsPhase       = WoundPhase::where('state', 1)->get();
        $bodyLocations     = BodyLocation::where('state', 1)->get();
        $bodySublocation   = BodySublocation::where('state', 1)->get();

        return Inertia::render('Wounds/Index', [
            'patients'        => $patients,
            'woundsType'      => $woundsType,
            'woundsSubtype'   => $woundsSubtype,
            'woundsPhase'     => $woundsPhase,
            'bodyLocations'   => $bodyLocations,
            'bodySublocation' => $bodySublocation,
            'grades'          => [
                ['label' => '1', 'value' => 1],
                ['label' => '2', 'value' => 2],
                ['label' => '3', 'value' => 3],
            ],
        ]);
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
        //
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
