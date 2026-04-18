<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->string('focused_pest')->nullable()->after('service_time');
        });
    }

    public function down(): void
    {
        Schema::table('daily_trackings', function (Blueprint $table) {
            $table->dropColumn('focused_pest');
        });
    }
};
