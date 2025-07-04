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
        Schema::create('wound_logs', function (Blueprint $table) {
           $table->id(); // 'id_woundLog'
            $table->foreignId('wound_id')->constrained('wounds');
            $table->foreignId('treatment_id')->constrained('treatments');
            $table->foreignId('measurement_id')->constrained('measurements');
            $table->foreignId('woundPhaseOld_id')->constrained('list_wound_phases');
            $table->foreignId('woundPhaseNew_id')->constrained('list_wound_phases');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wound_logs');
    }
};
