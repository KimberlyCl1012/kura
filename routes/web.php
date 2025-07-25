<?php

use App\Http\Controllers\Catalogues\AddressController;
use App\Http\Controllers\Catalogues\BodyLocationController;
use App\Http\Controllers\Catalogues\SiteController;
use App\Http\Controllers\Catalogues\WoundPhaseController;
use App\Http\Controllers\Catalogues\WoundTypeController;
use App\Http\Controllers\Kurator\KuratorController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Record\AppointmentController;
use App\Http\Controllers\Record\HealthRecordController;
use App\Http\Controllers\Record\MeasurementController;
use App\Http\Controllers\Record\MediaController;
use App\Http\Controllers\Record\TreatmentController;
use App\Http\Controllers\Record\WoundController;
use App\Http\Controllers\Record\WoundHistoryController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\HandleInertiaRequests;
use Carbon\Exceptions\NotAPeriodException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/register', function () {
    return redirect('/');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    HandleInertiaRequests::class,
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


    //Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/user_denied_permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('/user_denied_permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');

    //Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    //Patients
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');

    //Kurators
    Route::get('/kurators', [KuratorController::class, 'index'])->name('kurators.index');
    Route::post('/kurators', [KuratorController::class, 'store'])->name('kurators.store');
    Route::put('/kurators/{id}', [KuratorController::class, 'update'])->name('kurators.update');
    Route::delete('/kurators/{id}', [KuratorController::class, 'destroy'])->name('kurators.destroy');

    //Kurators Appointments
    Route::get('/appointments/by-kurator/{kuratorId}', [KuratorController::class, 'byKurator'])->name('appointments.byKurator');

    //Health Record
    Route::get('/health_records/create/{patientId}', [HealthRecordController::class, 'create'])->name('health_records.create');
    Route::post('/health_records', [HealthRecordController::class, 'store'])
        ->name('health_records.store');
    Route::put('/health_records/{healthRecord}', [HealthRecordController::class, 'update'])
        ->name('health_records.update');

    //Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    //Wounds
    Route::get('/wounds/{appointmentId}/{healthrecordId}', [WoundController::class, 'index'])->name('wounds.index');
    Route::post('/wounds', [WoundController::class, 'store'])->name('wounds.store');
    Route::get('/wounds/{woundId}', [WoundController::class, 'edit'])->name('wounds.edit');
    Route::put('/wounds/{wound}', [WoundController::class, 'update'])->name('wounds.update');


    //Measurement
    Route::post('/measurements', [MeasurementController::class, 'store'])->name('measurement.store');

    //Media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

    //Treatment
    Route::post('/treatments', [TreatmentController::class, 'store'])->name('treatment.store');


    //Wounds History
    Route::get('/wounds_histories', [WoundHistoryController::class, 'index'])->name('wounds_histories.index');
    Route::post('/wounds_histories', [WoundHistoryController::class, 'store'])->name('wounds_histories.store');

    // Relations
    Route::get('/wound_types/{woundtypeId}/subtypes', [WoundTypeController::class, 'subtypes'])->name('wound_types.subtypes');
    Route::get('/body_locations/{id}/sublocations', [BodyLocationController::class, 'sublocations'])->name('body_locations.subtypes');




    //Catalogues
    //Address
    Route::get('/address', [AddressController::class, 'index'])->name('address.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('address.store');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('address.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('address.destroy');

    //Body locations
    Route::get('/body_locations', [BodyLocationController::class, 'index'])->name('body_locations.index');
    Route::post('/body_locations', [BodyLocationController::class, 'store'])->name('body_locations.store');
    Route::put('/body_locations/{id}', [BodyLocationController::class, 'update'])->name('body_locations.update');
    Route::delete('/body_locations/{id}', [BodyLocationController::class, 'destroy'])->name('body_locations.destroy');

    //Sites
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::put('/sites/{id}', [SiteController::class, 'update'])->name('sites.update');
    Route::delete('/sites/{id}', [SiteController::class, 'destroy'])->name('sites.destroy');

    //Wound phases
    Route::get('/wound_phases', [WoundPhaseController::class, 'index'])->name('wound_phases.index');
    Route::post('/wound_phases', [WoundPhaseController::class, 'store'])->name('wound_phases.store');
    Route::put('/wound_phases/{id}', [WoundPhaseController::class, 'update'])->name('wound_phases.update');
    Route::delete('/wound_phases/{id}', [WoundPhaseController::class, 'destroy'])->name('wound_phases.destroy');

    //Wound types
    Route::get('/wound_types', [WoundTypeController::class, 'index'])->name('wound_types.index');
    Route::post('/wound_types', [WoundTypeController::class, 'store'])->name('wound_types.store');
    Route::put('/wound_types/{id}', [WoundTypeController::class, 'update'])->name('wound_types.update');
    Route::delete('/wound_types/{id}', [WoundTypeController::class, 'destroy'])->name('wound_types.destroy');
});
