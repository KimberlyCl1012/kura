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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id(); //'id_healthRecord'
            $table->foreignId('health_institution_id')->constrained('list_health_institutions');
            $table->string('record_uuid')->unique();
            $table->foreignId('patient_id')->constrained('patients');
            $table->longText('medicines',999);
            $table->longText('allergies',999);
            $table->longText('pathologicalBackground',999);
            $table->longText('laboratoryBackground',999);
            $table->longText('nourishmentBackground',999);
            $table->string('medicalInsurance')->nullable();
            $table->string('health_institution')->nullable();
            $table->string('religion')->nullable();
            $table->boolean('state')->default(1);            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
