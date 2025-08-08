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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id(); //'id_measurement'
            $table->foreignId('wound_id')->constrained('wounds');
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->date('measurementDate');
            $table->decimal('length', 8, 2);
            $table->decimal('width', 8, 2);
            $table->decimal('area', 8, 2);
            $table->decimal('depth', 8, 2)->nullable();
            $table->decimal('volume', 8, 2)->nullable();
            $table->string('tunneling')->nullable();
            $table->string('undermining');
            $table->decimal('granulation_percent', 5, 2);
            $table->decimal('slough_percent', 5, 2);
            $table->decimal('necrosis_percent', 5, 2);
            $table->decimal('epithelialization_percent', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
