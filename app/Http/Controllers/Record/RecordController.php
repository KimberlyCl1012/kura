<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Wound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class RecordController extends Controller
{

    public function index($healthRecordId)
    {
        try {
            $decrypthealthRecordId = Crypt::decryptString($healthRecordId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $wounds = Wound::with([
            'woundType:id,name',
            'woundSubtype:id,name',
            'bodyLocation:id,name',
        ])
            ->where('health_record_id', $decrypthealthRecordId)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Records/Index', [
            'wounds' => $wounds,
        ]);
    }

    public function show($appointmentId)
    {
        try {
            $decryptAppointmentId = Crypt::decryptString($appointmentId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }
    }

    public function generatePdf(Request $request)
    {
        $woundIds = $request->input('wound_ids');

        if (!is_array($woundIds) || empty($woundIds)) {
            return back()->withErrors(['Debes seleccionar al menos una herida.']);
        }

        $wounds = Wound::with([
            'woundPhase:id,name,description',
            'woundType:id,name,description',
            'woundSubtype:id,name,description,wound_type_id',
            'bodyLocation:id,name',
            'bodySublocation:id,name',
            'measurements' => fn($q) => $q->latest()->limit(1),
            'media:id,wound_id,content,type,position',
            'appointment:id,dateStartVisit,typeVisit,kurator_id',
            'appointment.kurator:id,user_detail_id,specialty,type_identification,identification',
            'appointment.kurator.userDetail:id,name,fatherLastName,motherLastName',
            'healthRecord:id,patient_id,medicines,allergies,pathologicalBackground,laboratoryBackground,nourishmentBackground,medicalInsurance,health_institution,religion',
            'healthRecord.patient:id,user_detail_id,dateOfBirth,identification',
            'healthRecord.patient.userDetail:id,name,fatherLastName,motherLastName',
        ])
            ->whereIn('id', $woundIds)
            ->get();

        $pdf = Pdf::loadView('pdf.wounds_report', compact('wounds'))->setPaper('A4');

        return $pdf->download('reporte_heridas.pdf');
    }
}
