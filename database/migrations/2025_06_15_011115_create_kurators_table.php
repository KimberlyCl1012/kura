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
        Schema::create('kurators', function (Blueprint $table) {
            $table->id(); //'id_kurator'
            $table->string('user_uuid')->unique();
            $table->foreignId('user_detail_id')->constrained('user_details');
            $table->string('specialty');
            $table->string('detail_specialty')->nullable();
            $table->string('type_kurator');
            $table->string('type_identification');
            $table->string('identification');
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
        Schema::dropIfExists('kurators');
    }
};
