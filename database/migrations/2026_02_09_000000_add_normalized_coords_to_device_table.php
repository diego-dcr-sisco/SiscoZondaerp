<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device', function (Blueprint $table) {
            $table->double('x_norm', 15, 8)->nullable()->after('map_y');
            $table->double('y_norm', 15, 8)->nullable()->after('x_norm');
        });
    }

    public function down(): void
    {
        Schema::table('device', function (Blueprint $table) {
            $table->dropColumn(['x_norm', 'y_norm']);
        });
    }
};
