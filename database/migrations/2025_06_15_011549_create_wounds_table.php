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
        Schema::create('wounds', function (Blueprint $table) {
             $table->id(); //'id_wound'
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->foreignId('health_record_id')->constrained('health_records');
            $table->foreignId('wound_phase_id')->constrained('list_wound_phases');
            $table->foreignId('wound_type_id')->constrained('list_wound_types');
            $table->foreignId('wound_subtype_id')->constrained('list_wound_subtypes');
            $table->foreignId('body_location_id')->constrained('list_body_locations');
            $table->foreignId('body_sublocation_id')->constrained('list_body_sublocations');
            $table->string('antecedentWound');
            $table->string('wound_type_other')->nullable();
            $table->string('woundBackground');
            $table->date('woundCreationDate');
            $table->date('woundBeginDate');
            $table->date('woundHealDate');
            $table->string('grade_foot')->nullable();
            $table->string('MESI');
            $table->string('borde');
            $table->string('edema');
            $table->string('dolor');
            $table->string('exudado_cantidad');
            $table->string('exudado_tipo');
            $table->string('olor');
            $table->string('piel_perisional');
            $table->string('infeccion');
            $table->string('tipo_dolor');
            $table->string('visual_scale');
            $table->string('ITB_izquierdo')->nullable();
            $table->string('pulse_dorsal_izquierdo')->nullable();
            $table->string('pulse_tibial_izquierdo')->nullable();
            $table->string('pulse_popliteo_izquierdo')->nullable();
            $table->string('ITB_derecho')->nullable();
            $table->string('pulse_dorsal_derecho')->nullable();
            $table->string('pulse_tibial_derecho')->nullable();
            $table->string('pulse_popliteo_derecho')->nullable();
            $table->string('blood_glucose')->nullable();
            $table->string('tunneling');
            $table->longText('note', 999)->nullable();
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wounds');
    }
};
