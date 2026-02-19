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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ceo_id')->nullable()->constrained('users')->onDelete('cascade')->after('id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('ceo_id')->nullable()->constrained('users')->onDelete('cascade')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ceo_id']);
            $table->dropColumn('ceo_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['ceo_id']);
            $table->dropColumn('ceo_id');
        });
    }
};
