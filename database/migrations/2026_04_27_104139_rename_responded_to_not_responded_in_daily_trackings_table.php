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
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->renameColumn('responded', 'not_responded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->renameColumn('not_responded', 'responded');
        });
    }
};
