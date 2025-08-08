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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id(); //'id_userDetail'
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('company_id')->constrained('list_companies');
            $table->foreignId('site_id')->constrained('list_sites');
            $table->string('sex');
            $table->string('name');
            $table->string('fatherLastName');
            $table->string('motherLastName')->nullable();
            $table->string('mobile')->nullable();
            $table->string('contactEmail')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
