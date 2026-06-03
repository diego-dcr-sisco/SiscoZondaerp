<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_service', function (Blueprint $table) {
            $table->boolean('custom_interval_enabled')->default(false)->after('service_description');
            $table->date('custom_interval_start_date')->nullable()->after('custom_interval_enabled');
            $table->unsignedInteger('custom_interval_days')->nullable()->after('custom_interval_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('contract_service', function (Blueprint $table) {
            $table->dropColumn([
                'custom_interval_enabled',
                'custom_interval_start_date',
                'custom_interval_days',
            ]);
        });
    }
};
