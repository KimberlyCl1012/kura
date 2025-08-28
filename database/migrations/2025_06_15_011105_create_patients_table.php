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
        Schema::create('patients', function (Blueprint $table) {
            $table->id(); //'id_patient'
            $table->string('user_uuid')->unique();
            $table->foreignId('state_id')->constrained('list_states');
            $table->foreignId('user_detail_id')->constrained('user_details');
            $table->date('dateOfBirth');
            $table->string('type_identification');
            $table->string('identification');
            $table->string('streetAddress')->nullable();
            $table->string('city')->nullable();
            $table->string('postalCode', 12)->nullable();
            $table->string('relativeName')->nullable();
            $table->string('type_identification_kinship')->nullable();
            $table->string('identification_kinship')->nullable();
            $table->enum('kinship', ['Padre', 'Madre', 'Hermano', 'Hijo'])->nullable();
            $table->string('relativeMobile')->nullable();
            $table->boolean('consent')->default(0);
            $table->boolean('state')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
