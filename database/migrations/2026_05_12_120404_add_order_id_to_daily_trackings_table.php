<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('daily_trackings') ||
            Schema::hasColumn('daily_trackings', 'order_id')
        ) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {

            $table->foreignId('order_id')
                ->nullable()
                ->after('service_id')
                ->constrained('order')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('daily_trackings') ||
            !Schema::hasColumn('daily_trackings', 'order_id')
        ) {
            return;
        }

        Schema::table('daily_trackings', function (Blueprint $table) {

            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');

        });
    }
};