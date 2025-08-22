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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // 'id_appointment'
            $table->bigInteger('wound_id')->nullable();
            $table->foreignId('site_id')->constrained('list_sites');
            $table->foreignId('health_record_id')->constrained('health_records');
            $table->foreignId('kurator_id')->constrained('kurators');
            $table->date('dateStartVisit')->nullable();
            $table->date('dateEndVisit')->nullable();
            $table->enum('typeVisit', ['Valoración', 'Urgencia', 'Seguimiento']);
            $table->integer('state')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
