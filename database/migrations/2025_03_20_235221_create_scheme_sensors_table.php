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
        Schema::create('scheme_sensors', function (Blueprint $table) {
            $table->id();
            $table->uuid('scheme_id');
            $table->foreign('scheme_id')->references('id')->on('schemes');
            $table->foreignId('sensor_id');
            $table->foreign('sensor_id')->references('id')->on('sensors');
            $table->integer('order');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_sensors');
    }
};
