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
        Schema::create('access_change_logs', function (Blueprint $table) {
             $table->id(); // 'id_changeLog'
            $table->foreignId('user_id')->constrained('users');
            $table->string('logType');
            $table->string('table');
            $table->string('primaryKey')->nullable();
            $table->string('secondaryKey')->nullable();
            $table->string('changeType');
            $table->string('fieldName')->nullable();
            $table->text('oldValue')->nullable();
            $table->longText('newValue')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_change_logs');
    }
};
