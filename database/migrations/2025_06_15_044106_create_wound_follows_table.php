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
        Schema::create('wound_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wound_id')->nullable()->constrained('wounds');
            $table->foreignId('wound_phase_id')->nullable()->constrained('list_wound_phases');
            $table->string('woundBackground');
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
            $table->string('ITB_derecho')->nullable();
            $table->string('pulse_dorsal_derecho')->nullable();
            $table->string('pulse_tibial_derecho')->nullable();
            $table->string('pulse_popliteo_derecho')->nullable();
            $table->string('ITB_izquierdo')->nullable();
            $table->string('pulse_dorsal_izquierdo')->nullable();
            $table->string('pulse_tibial_izquierdo')->nullable();
            $table->string('pulse_popliteo_izquierdo')->nullable();
            $table->string('tunneling');
            $table->string('measurementDate');
            $table->string('lenght');
            $table->string('width');
            $table->string('area');
            $table->string('maxDepth');
            $table->string('redPercentaje');
            $table->string('yellowPercentaje');
            $table->string('blackPercentaje');
            $table->string('media');
            $table->longText('description');
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wound_follows');
    }
};
