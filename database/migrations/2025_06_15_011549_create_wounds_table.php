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
            $table->foreignId('wound_type_id')->constrained('list_wound_types');
            $table->foreignId('wound_subtype_id')->constrained('list_wound_subtypes');
            $table->foreignId('body_location_id')->constrained('list_body_locations');
            $table->foreignId('body_sublocation_id')->constrained('list_body_sublocations');
            $table->string('woundBackground')->default(0);
            $table->date('woundCreationDate')->nullable();
            $table->date('woundBeginDate')->nullable();
            $table->date('woundHealthDate')->nullable();
            $table->string('type_bite')->nullable();
            $table->string('other_type')->nullable();
            $table->string('grade_foot')->nullable();
            $table->string('MESI')->nullable();
            $table->string('borde')->nullable();
            $table->string('edema')->nullable();
            $table->string('dolor')->nullable();
            $table->string('exudado_cantidad')->nullable();
            $table->string('exudado_tipo')->nullable();
            $table->string('olor')->nullable();
            $table->json('piel_perilesional')->nullable();
            $table->json('infeccion')->nullable();
            $table->string('tipo_dolor')->nullable();
            $table->string('duracion_dolor')->nullable();
            $table->string('visual_scale')->nullable();
            $table->string('ITB_izquierdo')->nullable();
            $table->string('pulse_dorsal_izquierdo')->nullable();
            $table->string('pulse_tibial_izquierdo')->nullable();
            $table->string('pulse_popliteo_izquierdo')->nullable();
            $table->string('ITB_derecho')->nullable();
            $table->string('pulse_dorsal_derecho')->nullable();
            $table->string('pulse_tibial_derecho')->nullable();
            $table->string('pulse_popliteo_derecho')->nullable();
            $table->string('monofilamento')->nullable();
            $table->string('blood_glucose')->nullable();
            $table->longText('note', 999)->nullable();
            $table->integer('state')->default(1);
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
