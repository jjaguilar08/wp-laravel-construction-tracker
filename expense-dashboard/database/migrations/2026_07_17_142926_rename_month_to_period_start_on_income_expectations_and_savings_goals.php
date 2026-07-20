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
        Schema::table('income_expectations', function (Blueprint $table) {
            $table->renameColumn('month', 'period_start');
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->renameColumn('month', 'period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_expectations', function (Blueprint $table) {
            $table->renameColumn('period_start', 'month');
        });

        Schema::table('savings_goals', function (Blueprint $table) {
            $table->renameColumn('period_start', 'month');
        });
    }
};
