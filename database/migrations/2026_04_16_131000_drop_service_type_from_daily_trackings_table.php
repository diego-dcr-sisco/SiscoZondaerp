<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('daily_trackings') || ! Schema::hasColumn('daily_trackings', 'service_type')) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->dropIndex(['status', 'service_type']);
            $table->dropColumn('service_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('daily_trackings') || Schema::hasColumn('daily_trackings', 'service_type')) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->string('service_type')->default('comercial')->after('status');
            $table->index(['status', 'service_type']);
        });
    }
};
