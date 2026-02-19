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
        // Company Goals (Level 1 - CEO Only)
        Schema::create('company_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->text('goal'); // e.g., "Meningkatkan pendapatan 30%"
            $table->string('year')->default(date('Y'));
            $table->timestamps();
        });

        // Objectives (Level 2 - Per Department/Division)
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_goal_id')->constrained('company_goals')->onDelete('cascade');
            $table->string('division'); // e.g., "Divisi Marketing"
            $table->text('objective'); // e.g., "Meningkatkan pendapatan software house 30%"
            $table->timestamps();
        });

        // Key Results (Level 3 - Measurable Targets)
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('objectives')->onDelete('cascade');
            $table->text('key_result'); // e.g., "Mendapatkan 10 klien korporasi baru"
            $table->integer('target'); // e.g., 10
            $table->integer('actual')->default(0); // e.g., 2
            $table->string('unit')->default('number'); // number, percentage, currency
            $table->integer('weight')->default(100); // Percentage weight of this KR to the Objective
            $table->timestamps();
        });

        // Action Plans (Level 4 - To-Do List)
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_result_id')->constrained('key_results')->onDelete('cascade');
            $table->text('activity'); // e.g., "Identifikasi target industri"
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plans');
        Schema::dropIfExists('key_results');
        Schema::dropIfExists('objectives');
        Schema::dropIfExists('company_goals');
    }
};
