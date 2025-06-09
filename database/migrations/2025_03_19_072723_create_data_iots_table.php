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
        Schema::create('data_iots', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->uuid('scheme_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('deleted')->default(false);
            $table->timestamps();

            $table->foreign('scheme_id')
                  ->references('id')
                  ->on('schemes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_iots');
    }
};
