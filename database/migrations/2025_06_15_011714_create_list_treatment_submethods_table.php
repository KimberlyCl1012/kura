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
        Schema::create('list_treatment_submethods', function (Blueprint $table) {
           $table->id(); 
            $table->foreignId('treatment_method_id')->constrained('list_treatment_methods');
            $table->string('name');            
            $table->longText('description',999)->nullable();              
            $table->boolean('state')->default(1);            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_treatment_submethods');
    }
};
