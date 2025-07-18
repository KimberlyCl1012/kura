<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $sites = Site::all();

        $patientRecords = HealthRecord::with('patient.userDetail')
            ->get()
            ->map(function ($record) {
                $detail = $record->patient->userDetail;
                return [
                    'full_name' => "{$record->record_uuid} - {$detail->name} {$detail->fatherLastName}",
                    'health_record_id' => $record->id,
                ];
            });

        $kurators = Kurator::with('userDetail')
            ->get()
            ->map(function ($k) {
                $d = $k->userDetail;
                return [
                    'full_name' => "{$k->user_uuid} - {$d->name} {$d->fatherLastName}",
                    'kurator_id' => $k->id,
                ];
            });

        $appointments = DB::table('appointments')
            ->join('list_sites', 'appointments.site_id', '=', 'list_sites.id')
            ->join('kurators', 'appointments.kurator_id', '=', 'kurators.id')
            ->join('user_details', 'kurators.user_detail_id', '=', 'user_details.id')
            ->join('health_records', 'appointments.health_record_id', '=', 'health_records.id')
            ->select(
                'appointments.*',
                'list_sites.siteName as site_name',
                DB::raw("CONCAT(kurators.user_uuid, '-', user_details.name) as kurator_full_name"),
                'health_records.record_uuid as health_record_uuid'
            )
            ->orderBy('appointments.created_at', 'desc')
            ->get();

        return Inertia::render('Appointments/Index', [
            'sites' => $sites,
            'kurators' => $kurators,
            'patientRecords' => $patientRecords,
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dateStartVisit' => 'required|date',
            'site_id' => 'required|exists:list_sites,id',
            'health_record_id' => 'required|exists:health_records,id',
            'kurator_id' => 'required|exists:kurators,id',
            'typeVisit' => 'required|in:Valoración,Urgencia,Seguimiento',
        ]);

        Appointment::create([
            'dateStartVisit' => $request->dateStartVisit,
            'site_id' => $request->site_id,
            'health_record_id' => $request->health_record_id,
            'kurator_id' => $request->kurator_id,
            'typeVisit' => $request->typeVisit,
            'state' => true, // se puede omitir, ya que por defecto es true
        ]);

        return redirect()->route('appointments.index');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(['message' => 'Consulta eliminada correctamente.'], 200);
    }
}
