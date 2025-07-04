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
        Schema::create('list_sites', function (Blueprint $table) {
             $table->id(); //'id_site'
            $table->foreignId('address_id')->constrained('list_addresses');
            $table->string('siteName');
            $table->string('email_admin');
            $table->string('phone');
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
        Schema::dropIfExists('list_sites');
    }
};
