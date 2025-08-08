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
        Schema::create('media_histories', function (Blueprint $table) {
            $table->id(); //'id_media_histories'
            $table->foreignId('wound_history_id')->constrained('wound_histories');
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
        Schema::dropIfExists('media_histories');
    }
};
