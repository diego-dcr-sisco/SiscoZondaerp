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
        if (
            Schema::hasColumn('daily_trackings', 'has_coverage') &&
            !Schema::hasColumn('daily_trackings', 'has_not_coverage')
        ) {

            Schema::table('daily_trackings', function (Blueprint $table) {

                $table->renameColumn('has_coverage', 'has_not_coverage');

            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasColumn('daily_trackings', 'has_not_coverage') &&
            !Schema::hasColumn('daily_trackings', 'has_coverage')
        ) {

            Schema::table('daily_trackings', function (Blueprint $table) {

                $table->renameColumn('has_not_coverage', 'has_coverage');

            });

        }
    }
};