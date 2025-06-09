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
        Schema::table('data_iots', function (Blueprint $table) {
            // Tambahkan kolom json_content jika belum ada
            if (!Schema::hasColumn('data_iots', 'json_content')) {
                $table->json('json_content')->nullable()->after('content');
            }
            
            // Tambahkan kolom additional_content jika belum ada
            if (!Schema::hasColumn('data_iots', 'additional_content')) {
                $table->json('additional_content')->nullable()->after('json_content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_iots', function (Blueprint $table) {
            if (Schema::hasColumn('data_iots', 'json_content')) {
                $table->dropColumn('json_content');
            }
            
            if (Schema::hasColumn('data_iots', 'additional_content')) {
                $table->dropColumn('additional_content');
            }
        });
    }
};
