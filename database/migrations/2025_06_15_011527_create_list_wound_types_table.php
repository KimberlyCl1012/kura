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
        Schema::create('list_wound_types', function (Blueprint $table) {
            $table->id(); //'id_woundType'
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
        Schema::dropIfExists('list_wound_types');
    }
};
