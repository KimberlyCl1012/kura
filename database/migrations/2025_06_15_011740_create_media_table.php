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
        Schema::create('media', function (Blueprint $table) {
            $table->id(); //'id_media'
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->foreignId('wound_id')->nullable()->constrained('wounds');
            $table->longText('description', 999)->nullable();
            $table->string('content');
            $table->string('position');
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
