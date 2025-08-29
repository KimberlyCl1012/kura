<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
        CREATE OR REPLACE VIEW vw_report_health_records AS
        SELECT
            hr.id                           AS health_record_id,
            hr.record_uuid,
            hr.patient_id,
            hr.health_institution_id,
            hr.medicines,
            hr.allergies,
            hr.pathologicalBackground,
            hr.laboratoryBackground,
            hr.nourishmentBackground,
            hr.medicalInsurance,
            hr.health_institution,
            hr.religion,
            hr.state            AS hr_state,
            hr.created_at       AS hr_created_at,
            hr.updated_at       AS hr_updated_at,

            -- Paciente
            p.user_uuid         AS patient_uuid,
            p.state_id          AS patient_state_id,
            p.user_detail_id,
            p.dateOfBirth,
            p.type_identification,
            p.identification,
            p.streetAddress,
            p.city,
            p.postalCode,
            p.relativeName,
            p.type_identification_kinship,
            p.identification_kinship,
            p.kinship,
            p.relativeMobile,
            p.consent           AS patient_consent,
            p.state             AS patient_state,
            p.created_by        AS patient_created_by,

            -- Detalle de usuario
            ud.user_id,
            ud.company_id,
            ud.site_id,
            ud.sex,
            ud.name             AS first_name,
            ud.fatherLastName   AS father_last_name,
            ud.motherLastName   AS mother_last_name,
            ud.mobile           AS phone,
            ud.contactEmail     AS email,
            ud.state            AS ud_state,

            -- Denormalizado útil para PDF
            CONCAT_WS(' ', ud.name, ud.fatherLastName, ud.motherLastName) AS full_name,

            -- Edad calculada
            TIMESTAMPDIFF(YEAR, p.dateOfBirth, CURDATE()) AS age_years,

            -- Sitio
            ls.siteName         AS site_name,

             CASE
                WHEN hr.health_institution_id = 5 THEN hr.health_institution
                ELSE lhi.name
            END AS institution_display_name

        FROM health_records hr
        JOIN patients p
            ON p.id = hr.patient_id
        JOIN user_details ud
            ON ud.id = p.user_detail_id
        LEFT JOIN list_health_institutions lhi
            ON lhi.id = hr.health_institution_id
        LEFT JOIN list_sites ls
            ON ls.id = ud.site_id
        ;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vw_record_wounds');
    }
};
