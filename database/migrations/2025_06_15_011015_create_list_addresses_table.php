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
        Schema::create('list_addresses', function (Blueprint $table) {
            $table->id(); //'id_address'
            $table->foreignId('state_id')->constrained('list_states');
            $table->string('type', 30);
            $table->string('streetAddress');
            $table->string('addressLine2')->nullable();
            $table->string('city');
            $table->string('postalCode', 12);
            $table->string('country', 3);
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->boolean('state')->default(1);            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_addresses');
    }
};
