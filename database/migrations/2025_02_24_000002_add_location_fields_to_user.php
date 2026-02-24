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
        // Añadir campos de última ubicación a la tabla user
        Schema::table('user', function (Blueprint $table) {
            $table->decimal('last_latitude', 10, 8)->nullable();
            $table->decimal('last_longitude', 11, 8)->nullable()->after('last_latitude');
            $table->decimal('last_location_accuracy', 8, 2)->nullable()->after('last_longitude');
            $table->timestamp('last_location_at')->nullable()->after('last_location_accuracy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'last_latitude',
                'last_longitude',
                'last_location_accuracy',
                'last_location_at'
            ]);
        });
    }
};
