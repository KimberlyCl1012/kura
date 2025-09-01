<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Wound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'healthRecord.patient.userDetail',
        ])
            ->where('health_record_id', $decrypthealthRecordId)
            ->where('state', 3)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Records/Index', [
            'wounds' => $wounds,
            'healthRecordId' => $decrypthealthRecordId,
        ]);
    }

    public function generatePdf(Request $request)
    {
        $woundIds        = $request->input('wound_ids', []);
        $healthRecordId  = $request->input('health_record_id');
        $filters         = $request->input('filters', []);

        $includeActive        = (bool)($filters['include_active'] ?? false);
        $includeFollowups     = (bool)($filters['include_followups'] ?? false);
        $includeConsultations = (bool)($filters['include_consultations'] ?? false);
        $dateFrom             = $filters['date_from'] ?? null;
        $dateTo               = $filters['date_to'] ?? null;
        $evidenceMode         = $filters['evidence_mode'] ?? 'first_last';

        $ACTIVE_STATE = 3;

        if (!$healthRecordId && !empty($woundIds)) {
            $healthRecordId = Wound::whereIn('id', $woundIds)->value('health_record_id');
        }
        if (!$healthRecordId) {
            return back()->withErrors(['Faltan parámetros: health_record_id o wound_ids.']);
        }

        $hr = DB::table('vw_report_health_records as v')
            ->select([
                'v.health_record_id',
                'v.record_uuid',
                'v.hr_state',
                'v.hr_created_at',
                'v.medicines',
                'v.allergies',
                'v.pathologicalBackground',
                'v.laboratoryBackground',
                'v.nourishmentBackground',
                'v.medicalInsurance',
                'v.health_institution',
                'v.religion',
                'v.patient_id',
                'v.patient_uuid',
                'v.dateOfBirth',
                'v.age_years',
                'v.full_name',
                'v.sex',
                'v.email',
                'v.phone',
                'v.streetAddress',
                'v.city',
                'v.postalCode',
                'v.relativeName',
                'v.kinship',
                'v.relativeMobile',
                'v.institution_display_name',
                'v.site_name',
            ])
            ->where('v.health_record_id', $healthRecordId)
            ->first();

        if (!$hr) {
            return back()->withErrors(['No se encontró el expediente del paciente.']);
        }

        $q = Wound::query()
            ->select([
                'id',
                'health_record_id',
                'wound_type_id',
                'wound_subtype_id',
                'body_location_id',
                'body_sublocation_id',
                'woundCreationDate',
                'woundBeginDate',
                'woundHealthDate',
                'MESI',
                'borde',
                'edema',
                'dolor',
                'exudado_cantidad',
                'exudado_tipo',
                'olor',
                'piel_perilesional',
                'infeccion',
                'tipo_dolor',
                'duracion_dolor',
                'visual_scale',
                'ITB_izquierdo',
                'pulse_dorsal_izquierdo',
                'pulse_tibial_izquierdo',
                'pulse_popliteo_izquierdo',
                'ITB_derecho',
                'pulse_dorsal_derecho',
                'pulse_tibial_derecho',
                'pulse_popliteo_derecho',
                'blood_glucose',
                'note',
                'state',
                'created_at'
            ])
            ->with([
                'woundType:id,name',
                'woundSubtype:id,name,wound_type_id',
                'bodyLocation:id,name',
                'bodySublocation:id,name',
                'treatments:id,wound_id,description,mmhg,beginDate,state',
            ]);

        if (!empty($woundIds)) {
            $q->whereIn('id', $woundIds);
        } else {
            $q->where('health_record_id', $healthRecordId);
        }

        if ($includeActive) {
            $q->where('state', $ACTIVE_STATE);
        }

        if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);

        // SOLO cargar medidas (no filtrar)
        if ($includeFollowups) {
            $q->with(['measurements' => function ($mq) {
                $mq->select([
                    'id',
                    'wound_id',
                    'measurementDate',
                    'length',
                    'width',
                    'area',
                    'depth',
                    'volume',
                    'granulation_percent',
                    'slough_percent',
                    'necrosis_percent',
                    'epithelialization_percent',
                    'created_at'
                ])->orderBy('measurementDate', 'asc');
            }]);
        } else {
            // última medición
            $q->with(['measurements' => function ($mq) {
                $mq->select([
                    'id',
                    'wound_id',
                    'measurementDate',
                    'length',
                    'width',
                    'area',
                    'depth',
                    'volume',
                    'created_at'
                ])->latest('measurementDate')->limit(1);
            }]);
        }

        // SOLO cargar consulta (no filtrar)
        if ($includeConsultations) {
            $q->with([
                'appointment:id,health_record_id,dateStartVisit,typeVisit,kurator_id',
                'appointment.kurator:id,user_detail_id,specialty,type_identification,identification',
                'appointment.kurator.userDetail:id,name,fatherLastName,motherLastName',
            ]);
        }

        // Evidencias
        $q->with(['media' => function ($mq) {
            $mq->select(['id', 'wound_id', 'content', 'type', 'position', 'created_at'])
                ->orderBy('created_at', 'asc');
        }]);

        $wounds = $q->orderByDesc('created_at')->get();

        $user = Auth::user();
        $user?->loadMissing('detail');
        if ($user && $user->detail) {
            $ud = $user->detail;
            $userName = trim("{$ud->name} {$ud->fatherLastName} {$ud->motherLastName}") ?: ($user->name ?? 'Usuario desconocido');
        } else {
            $userName = $user?->name ?? 'Usuario desconocido';
        }

        $pdf = Pdf::loadView('pdf.wounds_report', [
            'hr'           => $hr,
            'wounds'       => $wounds,
            'filters'      => $filters,
            'evidenceMode' => $evidenceMode,
            'generatedBy'  => $userName,
        ])->setPaper('A4');

        return $pdf->stream('reporte_heridas.pdf');
    }

    public function show($appointmentId)
    {
        try {
            $decryptAppointmentId = Crypt::decryptString($appointmentId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }
    }
}
