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
        Schema::table('schemes', function (Blueprint $table) {
            $table->string('visualization_type')->default('timeseries'); // timeseries, individual, bar
            $table->json('visualization_settings')->nullable(); // untuk menyimpan pengaturan tambahan
            $table->string('name')->nullable(); // Mengubah name menjadi nullable jika belum
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schemes', function (Blueprint $table) {
            $table->dropColumn(['visualization_type', 'visualization_settings', 'name']);
        });
    }
};
