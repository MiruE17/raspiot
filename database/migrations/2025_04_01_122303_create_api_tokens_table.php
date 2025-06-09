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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_hit')->nullable();
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->foreignId('create_uid')->constrained('users')->onDelete('cascade');
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index('token');
            $table->index('user_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
