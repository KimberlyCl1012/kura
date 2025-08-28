<?php

namespace App\Http\Controllers\Kurator;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\HealthRecord;
use App\Models\Kurator;
use App\Models\Patient;
use App\Models\Site;
use App\Models\State;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Str;

class KuratorController extends Controller
{
    protected function logChange(array $data)
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

    // Claves que requieren campo libre (detalle)
    private array $needsDetailKeys = ['Especialista', 'Especialidad', 'Subespecialidad', 'Maestría', 'Doctorado'];

    // Opciones permitidas por tipo
    private array $specialtyMap = [
        'Enfermería' => ['Técnico', 'Licenciatura', 'Especialista', 'Maestría', 'Doctorado'],
        'Medicina'   => ['Médico general', 'Especialidad', 'Subespecialidad', 'Maestría', 'Doctorado'],
    ];

    private function requiresDetail(string $key): bool
    {
        return in_array($key, $this->needsDetailKeys, true);
    }

    private function parseSpecialty(string $value): array
    {
        $parts = array_map('trim', explode(':', $value, 2));
        return [
            'key'    => $parts[0] ?? '',
            'detail' => $parts[1] ?? '',
        ];
    }

    private function normalizeSpecialty(string $key, string $detail): string
    {
        $key = trim(preg_replace('/\s+/', ' ', $key));
        $detail = trim(preg_replace('/\s+/', ' ', $detail));
        return $detail !== '' ? "{$key}: {$detail}" : $key;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $team = $user?->currentTeam;

        $pivotRole = null;
        if ($user && $team) {
            $pivot = $team->users()->where('user_id', $user->id)->first();
            $pivotRole = $pivot?->pivot?->role;
        }
        $roleKey = $pivotRole ?: ($team?->name ?: 'guest');

        $canSeeAllKurators = $team
            ? $team->permissions()->where('slug', 'show_clinical_staff')->exists()
            : false;

        $query = DB::table('kurators')
            ->join('user_details', 'user_details.id', '=', 'kurators.user_detail_id')
            ->join('users', 'user_details.user_id', '=', 'users.id')
            ->join('list_sites', 'user_details.site_id', '=', 'list_sites.id')
            ->select(
                DB::raw("CONCAT(kurators.user_uuid, '-', user_details.name) as kurator_full_name"),
                'kurators.id as kurator_id',
                'kurators.user_uuid',
                'kurators.specialty',
                'kurators.detail_specialty',
                'kurators.type_kurator',
                'kurators.type_identification',
                'kurators.identification',
                'kurators.state',
                'user_details.name',
                'user_details.fatherLastName',
                'user_details.motherLastName',
                'user_details.sex',
                'user_details.site_id',
                'user_details.mobile',
                'user_details.contactEmail',
                'list_sites.siteName',
                'users.email'
            )
            ->where('kurators.state', 1)
            ->where('user_details.state', 1)
            ->where('users.state', 1);

        if (!$canSeeAllKurators) {
            $query->where('kurators.created_by', $user->id);
            // Rstringir por sitio del usuario:
            // $query->where('user_details.site_id', $user->detail?->site_id ?? $user->site_id);
        }

        $kurators = $query->get()->map(function ($kurator) {
            $kurator->crypt_kurator = Crypt::encryptString($kurator->kurator_id);

            $kurator->specialty_key = $kurator->specialty;
            $kurator->specialty_detail = $kurator->detail_specialty;
            if (!$kurator->specialty_detail && strpos($kurator->specialty, ':') !== false) {
                [$k, $d] = array_map('trim', explode(':', $kurator->specialty, 2));
                $kurator->specialty_key = $k;
                $kurator->specialty_detail = $d;
            }
            return $kurator;
        });

        $sites = DB::table('list_sites')->select('id', 'siteName')->get();

        return Inertia::render('Kurators/Index', [
            'kurators' => $kurators,
            'sites'    => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Hombre,Mujer',
            'mobile' => 'nullable|string|max:20',
            'site_id' => 'required|exists:list_sites,id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'specialty' => 'required|string|max:255',
            'detail_specialty' => 'nullable|string|max:255',
            'type_kurator'        => ['required', 'string', 'max:50', Rule::in(['Enfermería', 'Medicina'])],
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
        ]);

        $validator->after(function ($v) use ($request) {
            $type = $request->input('type_kurator');
            $key = trim($request->input('specialty', ''));
            $detail = trim($request->input('detail_specialty', ''));

            $allowed = $this->specialtyMap[$type] ?? [];
            if (!in_array($key, $allowed, true)) {
                $v->errors()->add('specialty', 'La especialidad seleccionada no es válida para el tipo de personal.');
            }

            if ($this->requiresDetail($key) && $detail === '') {
                $v->errors()->add('detail_specialty', 'Debes indicar el nombre de la especialidad/subespecialidad/posgrado.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 1. Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $this->logChange([
                'logType'    => 'Kurador',
                'table'      => 'users',
                'primaryKey' => $user->id,
                'changeType' => 'create',
                'newValue'   => json_encode([
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            ]);

            // 2. Crear detalles
            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'sex' => $request->sex,
                'mobile' => $request->mobile,
                'contactEmail' => $request->email,
                'site_id' => $request->site_id,
                'company_id' => 1,
            ]);

            $this->logChange([
                'logType'    => 'Kurador',
                'table'      => 'user_details',
                'primaryKey' => $userDetail->id,
                'secondaryKey' => $user->id,
                'changeType' => 'create',
                'newValue'   => json_encode($userDetail->only([
                    'name',
                    'fatherLastName',
                    'motherLastName',
                    'sex',
                    'mobile',
                    'contactEmail',
                    'site_id'
                ])),
            ]);

            // 3. Crear kurador
            $anio = Carbon::now()->format('Y');
            $site = str_pad($userDetail->site_id, 2, '0', STR_PAD_LEFT);
            $random = strtoupper(Str::random(3));

            $kurator = Kurator::create([
                'user_uuid'          => 'KU' . $anio . '-' . $site . $random,
                'user_detail_id'     => $userDetail->id,
                'specialty'        => $request->input('specialty'),
                'detail_specialty' => $request->input('detail_specialty'),
                'type_kurator'       => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification'     => $request->identification,
                'created_by' => $request->user()->id,
            ]);

            $this->logChange([
                'logType'    => 'Kurador',
                'table'      => 'kurators',
                'primaryKey' => $kurator->id,
                'secondaryKey' => $userDetail->id,
                'changeType' => 'create',
                'newValue'   => json_encode($kurator->only([
                    'user_uuid',
                    'specialty',
                    'detail_specialty',
                    'type_kurator',
                    'type_identification',
                    'identification'
                ])),
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Personal santitario creado correctamente.');
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el personal sanitario.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $kurator = Kurator::findOrFail($id);
        $userDetail = $kurator->userDetail;
        $user = User::find($userDetail->user_id);

        $validator = Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'fatherLastName'      => 'required|string|max:255',
            'motherLastName'      => 'nullable|string|max:255',
            'sex'                 => ['required', Rule::in(['Hombre', 'Mujer'])],
            'mobile'              => 'nullable|string|max:20',
            'email'               => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'site_id'             => 'required|exists:list_sites,id',
            'specialty'           => 'required|string|max:255',
            'detail_specialty'  => 'nullable|string|max:255',
            'type_kurator'        => ['required', 'string', 'max:50', Rule::in(['Enfermería', 'Medicina'])],
            'type_identification' => 'required|string|max:50',
            'identification'      => 'required|string|max:50',
        ]);

        $validator->after(function ($v) use ($request) {
            $type = $request->input('type_kurator');
            $key = trim($request->input('specialty', ''));
            $detail = trim($request->input('detail_specialty', ''));

            $allowed = $this->specialtyMap[$type] ?? [];
            if (!in_array($key, $allowed, true)) {
                $v->errors()->add('specialty', 'La especialidad seleccionada no es válida para el tipo de personal.');
            }
            if ($this->requiresDetail($key) && $detail === '') {
                $v->errors()->add('specialty', 'Debes indicar el nombre de la especialidad/subespecialidad/posgrado.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // LOG CAMBIOS EN USERS
            $userCampos = ['name', 'email'];
            foreach ($userCampos as $campo) {
                $valorAnterior = $user->$campo;
                $valorNuevo = $request->$campo;

                if ((string) $valorAnterior !== (string) $valorNuevo) {
                    $this->logChange([
                        'logType'    => 'Kurador',
                        'table'      => 'users',
                        'primaryKey' => $user->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $valorAnterior,
                        'newValue'   => $valorNuevo,
                    ]);
                }
            }

            // LOG CAMBIOS EN USER_DETAILS
            $userDetailCampos = ['name', 'fatherLastName', 'motherLastName', 'sex', 'mobile', 'contactEmail', 'site_id'];
            foreach ($userDetailCampos as $campo) {
                $valorAnterior = $userDetail->$campo;
                $valorNuevo = $campo === 'contactEmail' ? $request->email : $request->$campo;

                if ((string) $valorAnterior !== (string) $valorNuevo) {
                    $this->logChange([
                        'logType'    => 'Kurador',
                        'table'      => 'user_details',
                        'primaryKey' => $userDetail->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $valorAnterior,
                        'newValue'   => $valorNuevo,
                    ]);
                }
            }

            $kuratorCampos = ['specialty', 'detail_specialty', 'type_kurator', 'type_identification', 'identification'];

            foreach ($kuratorCampos as $campo) {
                $valorAnterior = $kurator->$campo;
                $valorNuevo = $request->$campo;

                if ((string) $valorAnterior !== (string) $valorNuevo) {
                    $this->logChange([
                        'logType'    => 'Kurador',
                        'table'      => 'kurators',
                        'primaryKey' => $kurator->id,
                        'changeType' => 'update',
                        'fieldName'  => $campo,
                        'oldValue'   => $valorAnterior,
                        'newValue'   => $valorNuevo,
                    ]);
                }
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $userDetail->update([
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'sex' => $request->sex,
                'mobile' => $request->mobile,
                'contactEmail' => $request->email,
                'site_id' => $request->site_id,
            ]);

            $kurator->update([
                'specialty'        => $request->input('specialty'),
                'detail_specialty' => $request->input('detail_specialty'),
                'type_kurator'       => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification'     => $request->identification,
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Personal sanitario actualizado correctamente.');
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el personal sanitario.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $openAppointment = DB::table('appointments')
                ->where('kurator_id', $id)
                ->whereIn('state', [1, 2])
                ->exists();

            if ($openAppointment) {
                return response()->json([
                    'message' => 'No se puede eliminar el personal sanitario: primero tiene que reasignar la consulta en curso.'
                ], 422);
            }

            $kurator = Kurator::findOrFail($id);
            $userDetail = $kurator->userDetail;
            $userId = $userDetail->user_id;

            DB::beginTransaction();

            $oldKurator = $kurator->toArray();
            $oldUserDetail = $userDetail->toArray();
            $user = User::findOrFail($userId);

            $kurator->update(['state' => 0]);
            $this->logChange([
                'logType'    => 'Kurador',
                'table'      => 'kurators',
                'primaryKey' => $kurator->id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($oldKurator),
                'newValue'   => json_encode($kurator->toArray()),
            ]);

            $userDetail->update(['state' => 0]);
            $this->logChange([
                'logType'    => 'Kurators',
                'table'      => 'user_details',
                'primaryKey' => $userDetail->id,
                'changeType' => 'destroy',
                'oldValue'   => json_encode($oldUserDetail),
                'newValue'   => json_encode($userDetail->toArray()),
            ]);

            $user->state = 0;
            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'Personal sanitario desactivado correctamente.',
                'deleted_kurator_id' => $kurator->id,
            ]);
        } catch (\Throwable $e) {
            Log::error($e);
            DB::rollBack();
            return response()->json(['error' => 'Error al desactivar el personal snaitario.'], 500);
        }
    }

    public function byKurator($kuratorId)
    {
        try {
            $decryptkuratorId = Crypt::decryptString($kuratorId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID inválido');
        }

        $sites = Site::all();
        $states = State::all();

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

        $appointmentsRaw = DB::table('patients')
            ->join('user_details as patient_user_details', 'patients.user_detail_id', '=', 'patient_user_details.id')
            ->leftJoin('health_records', 'health_records.patient_id', '=', 'patients.id')
            ->leftJoin('appointments', 'appointments.health_record_id', '=', 'health_records.id')
            ->leftJoin('list_sites', 'appointments.site_id', '=', 'list_sites.id')
            ->leftJoin('kurators', 'appointments.kurator_id', '=', 'kurators.id')
            ->leftJoin('user_details as kurator_user_details', 'kurators.user_detail_id', '=', 'kurator_user_details.id')
            ->where('appointments.kurator_id', $decryptkuratorId)
            ->select(
                'patients.id as patient_id',
                DB::raw("CONCAT(
            COALESCE(patients.user_uuid, ''), ' - ',
            COALESCE(patient_user_details.name, ''), ' ',
            COALESCE(patient_user_details.fatherLastName, ''), ' ',
            COALESCE(patient_user_details.motherLastName, '')
        ) as patient_full_name"),
                DB::raw("COALESCE(health_records.record_uuid, 'Sin expediente') as health_record_uuid"),
                'health_records.id as health_record_id',
                'appointments.id as appointment_id',
                'appointments.dateStartVisit',
                'appointments.typeVisit',
                'list_sites.siteName as site_name',
                DB::raw("CONCAT(kurators.user_uuid, '-', COALESCE(kurator_user_details.name, '')) as kurator_full_name")
            )
            ->orderBy('patients.id')
            ->orderBy('appointments.dateStartVisit', 'desc')
            ->where('appointments.state', 1)
            ->get();

        $appointments = $appointmentsRaw
            ->groupBy('patient_id')
            ->map(function ($items) {
                $first = $items->first();

                $firstFolio = $items->pluck('health_record_uuid')
                    ->filter(fn($folio) => $folio !== 'Sin expediente' && !is_null($folio))
                    ->first() ?? 'Sin expediente';

                return [
                    'patient_id' => Crypt::encryptString($first->patient_id),
                    'patient_full_name' => $first->patient_full_name,
                    'health_record_uuid' => $firstFolio,
                    'appointments' => $items->filter(fn($r) => $r->appointment_id !== null)->map(fn($app) => [
                        'crypt_appointment_id' => Crypt::encryptString($app->appointment_id),
                        'dateStartVisit' => $app->dateStartVisit,
                        'typeVisit' => $app->typeVisit,
                        'site_name' => $app->site_name,
                        'kurator_full_name' => $app->kurator_full_name,
                        'health_record_uuid' => $app->health_record_uuid,
                        'crypt_health_record_id' => Crypt::encryptString($app->health_record_id),
                    ])->values(),
                ];
            })->values();

        return Inertia::render('Kurators/Appointments', [
            'states' => $states,
            'sites' => $sites,
            'patientRecords' => $patientRecords,
            'kurators' => $kurators,
            'appointments' => $appointments,
        ]);
    }
}
