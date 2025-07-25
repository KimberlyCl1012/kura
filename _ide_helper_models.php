<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $state_id
 * @property string $type
 * @property string $streetAddress
 * @property string|null $addressLine2
 * @property string $city
 * @property string $postalCode
 * @property string $country
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Site> $sites
 * @property-read int|null $sites_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereStreetAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $site_id
 * @property int $health_record_id
 * @property int $kurator_id
 * @property string|null $dateStartVisit
 * @property string|null $dateEndVisit
 * @property string $typeVisit
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\HealthRecord $healthRecord
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kurator> $kurators
 * @property-read int|null $kurators_count
 * @property-read \App\Models\Site|null $site
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TreatmentLog> $treatmentLogs
 * @property-read int|null $treatment_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wound> $wounds
 * @property-read int|null $wounds_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereDateEndVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereDateStartVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereHealthRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereKuratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereSiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereTypeVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appointment whereUpdatedAt($value)
 */
	class Appointment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BodySublocation> $bodySublocations
 * @property-read int|null $body_sublocations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodyLocation whereUpdatedAt($value)
 */
	class BodyLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $body_location_id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BodyLocation $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereBodyLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BodySublocation whereUpdatedAt($value)
 */
	class BodySublocation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDetail> $usersDetail
 * @property-read int|null $users_detail_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthInstitution whereUpdatedAt($value)
 */
	class HealthInstitution extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $health_institution_id
 * @property string $record_uuid
 * @property int $patient_id
 * @property string $medicines
 * @property string $allergies
 * @property string $pathologicalBackground
 * @property string $laboratoryBackground
 * @property string $nourishmentBackground
 * @property string|null $medicalInsurance
 * @property string|null $health_institution
 * @property string|null $religion
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereHealthInstitution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereHealthInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereLaboratoryBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereMedicalInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereMedicines($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereNourishmentBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord wherePathologicalBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereRecordUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthRecord whereUpdatedAt($value)
 */
	class HealthRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_uuid
 * @property int $user_detail_id
 * @property string $specialty
 * @property string $type_kurator
 * @property string $type_identification
 * @property string $identification
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Patient> $patient
 * @property-read int|null $patient_count
 * @property-read \App\Models\UserDetail $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereTypeIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereTypeKurator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereUserDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kurator whereUserUuid($value)
 */
	class Kurator extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $kurator_id
 * @property int $patient_id
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient whereKuratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KuratorPatient whereUpdatedAt($value)
 */
	class KuratorPatient extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submethod> $submethods
 * @property-read int|null $submethods_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentMethod whereUpdatedAt($value)
 */
	class ListTreatmentMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $treatment_method_id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ListTreatmentMethod $method
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereTreatmentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListTreatmentSubmethod whereUpdatedAt($value)
 */
	class ListTreatmentSubmethod extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $wound_id
 * @property int|null $appointment_id
 * @property string $measurementDate
 * @property string|null $lenght
 * @property string|null $width
 * @property string|null $area
 * @property string|null $depth
 * @property string|null $volume
 * @property string|null $tunneling
 * @property string|null $undermining
 * @property string|null $granulation_percent
 * @property string|null $slough_percent
 * @property string|null $necrosis_percent
 * @property string|null $epithelialization_percent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\TreatmentLog|null $treatmentLog
 * @property-read \App\Models\Wound $wound
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereEpithelializationPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereGranulationPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereLenght($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereMeasurementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereNecrosisPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereSloughPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereTunneling($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereUndermining($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Measurement whereWoundId($value)
 */
	class Measurement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $wound_id
 * @property string|null $description
 * @property string|null $content
 * @property string|null $position
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Measurement|null $measurement
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereWoundId($value)
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUserId($value)
 */
	class Membership extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submethod> $submethods
 * @property-read int|null $submethods_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Method whereUpdatedAt($value)
 */
	class Method extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_uuid
 * @property int $state_id
 * @property int $user_detail_id
 * @property string $dateOfBirth
 * @property string $type_identification
 * @property string $identification
 * @property string|null $streetAddress
 * @property string|null $city
 * @property string|null $postalCode
 * @property string|null $relativeName
 * @property string|null $kinship
 * @property string|null $relativeMobile
 * @property int $consent
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kurator> $kurators
 * @property-read int|null $kurators_count
 * @property-read \App\Models\Site|null $site
 * @property-read \App\Models\UserDetail $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereConsent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereKinship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereRelativeMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereRelativeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereStreetAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereTypeIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUserDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUserUuid($value)
 */
	class Patient extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $address_id
 * @property string $siteName
 * @property string $email_admin
 * @property string $phone
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address $address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointment
 * @property-read int|null $appointment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Patient> $patient
 * @property-read int|null $patient_count
 * @property-read \App\Models\UserDetail|null $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereEmailAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereSiteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereUpdatedAt($value)
 */
	class Site extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $clave
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereClave($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereUpdatedAt($value)
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $treatment_method_id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ListTreatmentMethod $method
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereTreatmentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Submethod whereUpdatedAt($value)
 */
	class Submethod extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property bool $personal_team
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamInvitation> $teamInvitations
 * @property-read int|null $team_invitations_count
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team wherePersonalTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUserId($value)
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $team_id
 * @property string $email
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamInvitation whereUpdatedAt($value)
 */
	class TeamInvitation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $wound_id
 * @property string|null $description
 * @property string $beginDate
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Treatment|null $treatment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TreatmentLog> $treatmentLogs
 * @property-read int|null $treatment_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Method> $treatmentMethods
 * @property-read int|null $treatment_methods_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Submethod> $treatmentSubmethods
 * @property-read int|null $treatment_submethods_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereBeginDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Treatment whereWoundId($value)
 */
	class Treatment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $appointment_id
 * @property string $treatmentDate
 * @property string $evolution
 * @property string $bloodPressure
 * @property string $temperature
 * @property string $oxigenation
 * @property string $heartRate
 * @property string $medicines
 * @property string $laboratory
 * @property string $nourishment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Appointment $appointment
 * @property-read \App\Models\Treatment|null $treatment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereEvolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereHeartRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereLaboratory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereMedicines($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereNourishment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereOxigenation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereTreatmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreatmentLog whereUpdatedAt($value)
 */
	class TreatmentLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team|null $currentTeam
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDeniedPermission> $deniedPermissions
 * @property-read int|null $denied_permissions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read string $profile_photo_url
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDeniedPermission whereUserId($value)
 */
	class UserDeniedPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $company_id
 * @property int|null $site_id
 * @property string $sex
 * @property string $name
 * @property string $fatherLastName
 * @property string|null $motherLastName
 * @property string|null $mobile
 * @property string $contactEmail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Kurator|null $kurator
 * @property-read \App\Models\Patient|null $patient
 * @property-read \App\Models\Site|null $site
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereFatherLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereMotherLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereSiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDetail whereUserId($value)
 */
	class UserDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VWound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VWound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VWound query()
 */
	class VWound extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $appointment_id
 * @property int $health_record_id
 * @property int $wound_phase_id
 * @property int $wound_type_id
 * @property int $wound_subtype_id
 * @property int $body_location_id
 * @property int $body_sublocation_id
 * @property string|null $wound_type_other
 * @property string $woundBackground
 * @property string|null $woundCreationDate
 * @property string|null $woundBeginDate
 * @property string|null $woundHealthDate
 * @property string|null $grade_foot
 * @property string|null $MESI
 * @property string|null $borde
 * @property string|null $edema
 * @property string|null $dolor
 * @property string|null $exudado_cantidad
 * @property string|null $exudado_tipo
 * @property string|null $olor
 * @property string|null $piel_perisional
 * @property string|null $infeccion
 * @property string|null $tipo_dolor
 * @property string|null $visual_scale
 * @property string|null $ITB_izquierdo
 * @property string|null $pulse_dorsal_izquierdo
 * @property string|null $pulse_tibial_izquierdo
 * @property string|null $pulse_popliteo_izquierdo
 * @property string|null $ITB_derecho
 * @property string|null $pulse_dorsal_derecho
 * @property string|null $pulse_tibial_derecho
 * @property string|null $pulse_popliteo_derecho
 * @property string|null $blood_glucose
 * @property string|null $tunneling
 * @property string|null $note
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BodyLocation $bodyLocation
 * @property-read \App\Models\BodySublocation $bodySublocation
 * @property-read \App\Models\HealthRecord $healthRecord
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WoundHistory> $histories
 * @property-read int|null $histories_count
 * @property-read \App\Models\WoundPhase $woundPhase
 * @property-read \App\Models\WoundSubtype $woundSubtype
 * @property-read \App\Models\WoundType $woundType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereBloodGlucose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereBodyLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereBodySublocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereBorde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereDolor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereEdema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereExudadoCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereExudadoTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereGradeFoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereHealthRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereITBDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereITBIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereInfeccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereMESI($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereOlor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePielPerisional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulseDorsalDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulseDorsalIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulsePopliteoDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulsePopliteoIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulseTibialDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound wherePulseTibialIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereTipoDolor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereTunneling($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereVisualScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundBeginDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundCreationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundHealthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundPhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundSubtypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wound whereWoundTypeOther($value)
 */
	class Wound extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundFollow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundFollow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundFollow query()
 */
	class WoundFollow extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $wound_id
 * @property int $wound_phase_id
 * @property int $wound_type_id
 * @property int $wound_subtype_id
 * @property int $body_location_id
 * @property int $body_sublocation_id
 * @property string|null $wound_type_other
 * @property string $woundBackground
 * @property string|null $woundBeginDate
 * @property string|null $grade_foot
 * @property string|null $MESI
 * @property string|null $borde
 * @property string|null $edema
 * @property string|null $dolor
 * @property string|null $exudado_cantidad
 * @property string|null $exudado_tipo
 * @property string|null $olor
 * @property string|null $piel_perisional
 * @property string|null $infeccion
 * @property string|null $tipo_dolor
 * @property string|null $visual_scale
 * @property string|null $ITB_derecho
 * @property string|null $pulse_dorsal_derecho
 * @property string|null $pulse_tibial_derecho
 * @property string|null $pulse_popliteo_derecho
 * @property string|null $ITB_izquierdo
 * @property string|null $pulse_dorsal_izquierdo
 * @property string|null $pulse_tibial_izquierdo
 * @property string|null $pulse_popliteo_izquierdo
 * @property string|null $tunneling
 * @property string|null $lenght
 * @property string|null $width
 * @property string|null $area
 * @property string|null $depth
 * @property string|null $volume
 * @property string|null $redPercentaje
 * @property string|null $yellowPercentaje
 * @property string|null $blackPercentaje
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BodyLocation $bodyLocation
 * @property-read \App\Models\BodySublocation $bodySublocation
 * @property-read \App\Models\HealthRecord|null $healthRecord
 * @property-read \App\Models\Wound|null $wound
 * @property-read \App\Models\WoundPhase $woundPhase
 * @property-read \App\Models\WoundSubtype $woundSubtype
 * @property-read \App\Models\WoundType $woundType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereBlackPercentaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereBodyLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereBodySublocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereBorde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereDolor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereEdema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereExudadoCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereExudadoTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereGradeFoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereITBDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereITBIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereInfeccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereLenght($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereMESI($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereOlor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePielPerisional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulseDorsalDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulseDorsalIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulsePopliteoDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulsePopliteoIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulseTibialDerecho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory wherePulseTibialIzquierdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereRedPercentaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereTipoDolor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereTunneling($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereVisualScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundBeginDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundPhaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundSubtypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereWoundTypeOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundHistory whereYellowPercentaje($value)
 */
	class WoundHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $wound_id
 * @property int $treatment_id
 * @property int $measurement_id
 * @property int $woundPhaseOld_id
 * @property int $woundPhaseNew_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read WoundLog $currentWoundLog
 * @property-read \App\Models\Measurement $measurement
 * @property-read WoundLog $oldWoundLog
 * @property-read \App\Models\Treatment $treatment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wound> $wound
 * @property-read int|null $wound_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereMeasurementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereTreatmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereWoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereWoundPhaseNewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundLog whereWoundPhaseOldId($value)
 */
	class WoundLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WoundLog> $currentWoundLog
 * @property-read int|null $current_wound_log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WoundLog> $oldWoundLog
 * @property-read int|null $old_wound_log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wound> $wound
 * @property-read int|null $wound_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundPhase whereUpdatedAt($value)
 */
	class WoundPhase extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $wound_type_id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WoundType $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundSubtype whereWoundTypeId($value)
 */
	class WoundSubtype extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WoundSubtype> $woundSubtypes
 * @property-read int|null $wound_subtypes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WoundType whereUpdatedAt($value)
 */
	class WoundType extends \Eloquent {}
}

