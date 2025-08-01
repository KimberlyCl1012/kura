<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class RecordController extends Controller
{
    public function index($healthRecordId)
    {
        try {
            $decrypthealthRecordId = Crypt::decryptString($healthRecordId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $appointments = Appointment::with([
            'wounds.histories',
            'wounds.woundType:id,name',
            'wounds.woundSubtype:id,name',
            'wounds.bodyLocation:id,name',
            'healthRecord.patient.userDetail:id,name,fatherLastName,patient_id',
            'kurator.userDetail:id,name,fatherLastName,kurator_id',
        ])
            ->where('health_record_id', $decrypthealthRecordId)
            ->orderBy('dateStartVisit', 'desc')
            ->get();

        return Inertia::render('Records/Index', [
            'appointments' => $appointments,
        ]);
    }
}
