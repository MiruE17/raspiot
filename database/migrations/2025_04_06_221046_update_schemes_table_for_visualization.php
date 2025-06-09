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
        // Modify existing schemes table
        Schema::table('schemes', function (Blueprint $table) {
            // Add new columns
            $table->text('description')->after('name')->nullable();
            });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('schemes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
