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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id(); //'id_treatment'
            $table->foreignId('wound_id')->nullable()->constrained('wounds');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->string('mmhg')->nullable();
            $table->longText('description',999)->nullable();
            $table->date('beginDate');
            $table->boolean('state')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
