<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\KuratorPatient;
use App\Models\Site;
use App\Models\Wound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AppointmentController extends Controller
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
            ->where('state', 1)
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

        $finalizedWounds = Wound::with([
            'woundType:id,name',
            'woundSubtype:id,name',
            'bodyLocation:id,name',
            'bodySublocation:id,name'
        ])
            ->where('state', 3)
            ->get()
            ->map(function ($wound) {
                $type = $wound->woundType->name ?? $wound->wound_type_other ?? 'Tipo desconocido';
                $subtype = $wound->woundSubtype->name ?? '';
                $location = $wound->bodyLocation->name ?? '';
                $sublocation = $wound->bodySublocation->name ?? '';

                return [
                    'id' => $wound->id,
                    'health_record_id' => $wound->health_record_id,
                    'name' => "{$type}"
                        . ($subtype ? " ({$subtype})" : '')
                        . ($location || $sublocation ? " - {$location}" . ($sublocation ? " / {$sublocation}" : '') : ''),
                ];
            });


        return Inertia::render('Appointments/Index', [
            'sites' => $sites,
            'kurators' => $kurators,
            'patientRecords' => $patientRecords,
            'appointments' => $appointments,
            'finalizedWounds' => $finalizedWounds,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'dateStartVisit' => 'required|date',
            'site_id' => 'required|exists:list_sites,id',
            'health_record_id' => 'required|exists:health_records,id',
            'kurator_id' => 'required|exists:kurators,id',
            'typeVisit' => 'required|in:Valoración,Urgencia,Seguimiento',
        ];

        if ($request->typeVisit === 'Seguimiento') {
            $rules['wound_id'] = 'required|exists:wounds,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $healthRecord = HealthRecord::findOrFail($request->health_record_id);
            $patientId = $healthRecord->patient_id;

            $kp = KuratorPatient::updateOrCreate(
                [
                    'kurator_id' => $request->kurator_id,
                    'patient_id' => $patientId,
                ],
                ['state' => 1]
            );

            // Log del upsert (create/update)
            $this->logChange([
                'logType'      => 'Cita',
                'table'        => 'kurator_patient',
                'primaryKey'   => $kp->id,
                'secondaryKey' => $patientId,
                'changeType'   => $kp->wasRecentlyCreated ? 'create' : 'update',
                'newValue'     => json_encode([
                    'kurator_id' => (int) $request->kurator_id,
                    'patient_id' => (int) $patientId,
                    'state'      => 1,
                ]),
            ]);


            $appointment = Appointment::create([
                'dateStartVisit' => $request->dateStartVisit,
                'site_id' => $request->site_id,
                'health_record_id' => $request->health_record_id,
                'kurator_id' => $request->kurator_id,
                'typeVisit' => $request->typeVisit,
                'wound_id' => $request->wound_id,
                'state' => 1,
            ]);

            // Log de creación de cita
            $this->logChange([
                'logType'    => 'Cita',
                'table'      => 'appointments',
                'primaryKey' => $appointment->id,
                'changeType' => 'create',
                'newValue'   => json_encode($appointment->only([
                    'dateStartVisit',
                    'dateEndVisit',
                    'site_id',
                    'health_record_id',
                    'kurator_id',
                    'typeVisit',
                    'wound_id',
                    'state',
                ])),
            ]);

            //Actualizar estado de la herida si es seguimiento
            if ($request->typeVisit === 'Seguimiento') {
                $wound = Wound::findOrFail($request->wound_id);
                $old   = $wound->state;

                $wound->update(['state' => 2]);

                $this->logChange([
                    'logType'    => 'Cita',
                    'table'      => 'wounds',
                    'primaryKey' => $wound->id,
                    'changeType' => 'update',
                    'fieldName'  => 'state',
                    'oldValue'   => $old,
                    'newValue'   => 2,
                ]);
            }

            DB::commit();

            return redirect()->route('appointments.index')
                ->with('success', 'Consulta creada correctamente.');
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error al guardar la consulta: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Appointment $appointment)
    {
        try {
            $hasWounds = $appointment->wounds()->exists();
            if ($hasWounds) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar, ya existen heridas asociadas a la consulta.'
                ], 400); // Error 400 - Bad Request
            }

            $before = $appointment->toArray();
            $id = $appointment->id;

            $appointment->delete();

            // Log de eliminación
            $this->logChange([
                'logType'    => 'Cita',
                'table'      => 'appointments',
                'primaryKey' => $id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($before),
                'newValue'   => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Consulta eliminada correctamente.'
            ], 200);
        } catch (\Throwable $e) {
            Log::info('Eliminar consulta');
            Log::debug($e);
            Log::error('Error al eliminar la consulta', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar eliminar la consulta.',
                'error' => $e->getMessage(), // O quítalo en producción si no deseas exponer detalles
            ], 500);
        }
    }

    public function countWounds($appointmentId)
    {
        try {
            $count = Wound::where('appointment_id', $appointmentId)->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al contar heridas.'], 500);
        }
    }


    public function finish(Request $request)
    {
        try {
            $appointmentId = $request->input('appointment_id');

            if (!$appointmentId) {
                return response()->json(['message' => 'ID de consulta requerido'], 422);
            }

            // Verificar existencia de la cita
            $appointment = Appointment::findOrFail($appointmentId);

            // Verificar si hay heridas
            $woundCount = Wound::where('appointment_id', $appointmentId)->count();

            if ($woundCount === 0) {
                DB::rollBack();
                return response()->json(['message' => 'No hay heridas asociadas a esta consulta.'], 400);
            }

            // Actualizar todas las heridas de la cita
            $updated = Wound::where('appointment_id', $appointmentId)->update(['state' => 3]);

            $this->logChange([
                'logType'    => 'Cita',
                'table'      => 'wounds',
                'primaryKey' => null,
                'secondaryKey' => $appointmentId,
                'changeType' => 'bulk-update',
                'fieldName'  => 'state',
                'oldValue'   => 'varios',
                'newValue'   => json_encode(['state' => 3, 'updated_rows' => $updated]),
            ]);

            // Cambiar estado de la cita y fecha de cierre
            $oldState = (int) $appointment->state;
            $oldDateEnd = $appointment->dateEndVisit;

            // Actualizar el estado de la consulta
            $appointment->state = 3;
            $appointment->save();

            // Logs de la cita (state y dateEndVisit)
            if ($oldState !== 3) {
                $this->logChange([
                    'logType'    => 'Cita',
                    'table'      => 'appointments',
                    'primaryKey' => $appointment->id,
                    'changeType' => 'update',
                    'fieldName'  => 'state',
                    'oldValue'   => $oldState,
                    'newValue'   => 3,
                ]);
            }

            if ($oldDateEnd !== $appointment->dateEndVisit) {
                $this->logChange([
                    'logType'    => 'Cita',
                    'table'      => 'appointments',
                    'primaryKey' => $appointment->id,
                    'changeType' => 'update',
                    'fieldName'  => 'dateEndVisit',
                    'oldValue'   => $oldDateEnd,
                    'newValue'   => $appointment->dateEndVisit,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Consulta finalizada correctamente.',
                'redirect_to' => route('kurators.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al finalizar la consulta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
