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
        Schema::create('bmc_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ceo_id')->constrained('users')->onDelete('cascade');
            $table->string('block'); // e.g., 'key_partners', 'value_propositions', etc.
            $table->text('content');
            $table->string('color')->default('blue'); // Optional: color preference
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bmc_items');
    }
};
