<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('daily_trackings') || Schema::hasColumn('daily_trackings', 'is_recurrent')) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->boolean('is_recurrent')->default(false)->after('responded');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('daily_trackings') || ! Schema::hasColumn('daily_trackings', 'is_recurrent')) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->dropColumn('is_recurrent');
        });
    }
};
