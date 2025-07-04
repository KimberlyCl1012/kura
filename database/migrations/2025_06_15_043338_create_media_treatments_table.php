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
        Schema::create('media_treatments', function (Blueprint $table) {
           $table->id(); //'id_media'
            $table->foreignId('wound_id')->nullable()->constrained('wounds');
            $table->foreignId('treatment_id')->nullable()->constrained('treatments');
            $table->longText('description',999)->nullable();
            $table->string('content')->nullable();
            $table->string('position')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_treatments');
    }
};
