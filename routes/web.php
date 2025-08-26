<?php

use App\Http\Controllers\Catalogues\AddressController;
use App\Http\Controllers\Catalogues\BodyLocationController;
use App\Http\Controllers\Catalogues\BodySublocationController;
use App\Http\Controllers\Catalogues\MethodController;
use App\Http\Controllers\Catalogues\SiteController;
use App\Http\Controllers\Catalogues\SubmethodController;
use App\Http\Controllers\Catalogues\WoundAssessmentController;
use App\Http\Controllers\Catalogues\WoundPhaseController;
use App\Http\Controllers\Catalogues\WoundSubtypeController;
use App\Http\Controllers\Catalogues\WoundTypeController;
use App\Http\Controllers\Kurator\KuratorController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Record\AppointmentController;
use App\Http\Controllers\Record\HealthRecordController;
use App\Http\Controllers\Record\MeasurementController;
use App\Http\Controllers\Record\MediaController;
use App\Http\Controllers\Record\MediaHistoryController;
use App\Http\Controllers\Record\RecordController;
use App\Http\Controllers\Record\TreatmentController;
use App\Http\Controllers\Record\WoundController;
use App\Http\Controllers\Record\WoundFollowController;
use App\Http\Controllers\Record\WoundHistoryController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\HandleInertiaRequests;
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
    Route::get('/teams/{team}/permissions', [PermissionController::class, 'show'])
        ->name('permissions.show');

    // Sincroniza permisos del team seleccionado
    Route::post('/teams/{team}/permissions/sync', [PermissionController::class, 'sync'])
        ->name('permissions.sync');

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
    Route::put('/appointments/finish', [AppointmentController::class, 'finish'])->name('appointments.finish');
    Route::get('/appointments/{appointmentId}/wounds/count', [AppointmentController::class, 'countWounds'])->name('appointments.countWounds');
    //Move Patient
    Route::put('/appointments/{appointment}/reassign', [AppointmentController::class, 'reassign'])->name('appointments.reassign');

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
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    //Media History
    Route::get('/media_history', [MediaHistoryController::class, 'index'])->name('media_history.index');
    Route::post('/media_history/upload', [MediaHistoryController::class, 'upload'])->name('media_history.upload');

    //Treatment
    Route::post('/treatments/edit', [TreatmentController::class, 'update'])->name('treatment.update');
    Route::post('/treatments', [TreatmentController::class, 'store'])->name('treatment.store');

    //Wound Follows
    Route::get('/wounds_follow/{appointmentId}/{woundId}/edit', [WoundFollowController::class, 'edit'])->name('wounds_follow.edit');
    Route::put('/wounds_follow/{woundId}', [WoundFollowController::class, 'update'])->name('wounds_follow.update');

    //Wounds History
    Route::post('/wounds_histories', [WoundHistoryController::class, 'store'])->name('wounds_histories.store');
    Route::get('/wounds_histories/{woundHisId}', [WoundHistoryController::class, 'edit'])->name('wounds_histories.edit');
    Route::put('/wounds_histories/{woundHisId}/edit', [WoundHistoryController::class, 'update'])->name('wounds_histories.update');

    //Record
    Route::get('/records/{healthRecordId}', [RecordController::class, 'index'])->name('records.index');
    Route::get('/records/{appointmentId}/show', [RecordController::class, 'show'])->name('records.show');
    Route::post('/records/pdf', [RecordController::class, 'generatePdf'])->name('records.generate-pdf');

    // Relations
    Route::get('/wound_types/{woundtypeId}/subtypes', [WoundTypeController::class, 'subtypes'])->name('wound_types.subtypes');
    Route::get('/body_locations/{id}/sublocations', [BodyLocationController::class, 'sublocations'])->name('body_locations.subtypes');
    Route::get('/methods/{id}/submethods', [MethodController::class, 'submethods'])->name('methods.submethods');


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

    //Body Sublocations
    Route::get('/body_sublocations', [BodySublocationController::class, 'index'])->name('body_sublocations.index');
    Route::post('/body_sublocations', [BodySublocationController::class, 'store'])->name('body_sublocations.store');
    Route::put('/body_sublocations/{id}', [BodySublocationController::class, 'update'])->name('body_sublocations.update');
    Route::delete('/body_sublocations/{id}', [BodySublocationController::class, 'destroy'])->name('body_sublocations.destroy');

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

    //Wound Subtypes
    Route::get('/wound_subtypes', [WoundSubtypeController::class, 'index'])->name('wound_subtypes.index');
    Route::post('/wound_subtypes', [WoundSubtypeController::class, 'store'])->name('wound_subtypes.store');
    Route::put('/wound_subtypes/{id}', [WoundSubtypeController::class, 'update'])->name('wound_subtypes.update');
    Route::delete('/wound_subtypes/{id}', [WoundSubtypeController::class, 'destroy'])->name('wound_subtypes.destroy');

    //Methods Treatment
    Route::get('/methods', [MethodController::class, 'index'])->name('methods.index');
    Route::post('/methods', [MethodController::class, 'store'])->name('methods.store');
    Route::put('/methods/{id}', [MethodController::class, 'update'])->name('methods.update');
    Route::delete('/methods/{id}', [MethodController::class, 'destroy'])->name('methods.destroy');
    Route::get('/body_locations/{id}/sublocations', [BodyLocationController::class, 'sublocations'])->name('body_locations.subtypes');

    //Submethods Treatment
    Route::get('/submethods', [SubmethodController::class, 'index'])->name('submethods.index');
    Route::post('/submethods', [SubmethodController::class, 'store'])->name('submethods.store');
    Route::put('/submethods/{id}', [SubmethodController::class, 'update'])->name('submethods.update');
    Route::delete('/submethods/{id}', [SubmethodController::class, 'destroy'])->name('submethods.destroy');

    //Wound Assessment
    Route::get('/wound_assessment', [WoundAssessmentController::class, 'index'])->name('wound_assessment.index');
    Route::post('/wound_assessment', [WoundAssessmentController::class, 'store'])->name('wound_assessment.store');
    Route::put('/wound_assessment/{id}', [WoundAssessmentController::class, 'update'])->name('wound_assessment.update');
    Route::delete('/wound_assessment/{id}', [WoundAssessmentController::class, 'destroy'])->name('wound_assessment.destroy');
});
