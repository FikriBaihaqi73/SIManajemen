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
        // Vision
        Schema::create('company_visions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        // Missions
        Schema::create('company_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Core Values
        Schema::create('company_core_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_core_values');
        Schema::dropIfExists('company_missions');
        Schema::dropIfExists('company_visions');
    }
};
