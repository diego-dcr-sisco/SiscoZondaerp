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
        Schema::table('device', function (Blueprint $table) {
            // Cambiar de FLOAT(10,8) a DECIMAL(10,8) para mejor precisión
            // DECIMAL(10,8) = 2 dígitos antes del punto, 8 decimales
            // Suficiente para coordenadas: -90.00000000 a 90.00000000 (latitud)
            // Para longitud necesitamos 3 dígitos antes: -180.00000000 a 180.00000000
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device', function (Blueprint $table) {
            // Revertir a FLOAT original
            $table->float('latitude', 10, 8)->nullable()->change();
            $table->float('longitude', 10, 8)->nullable()->change();
        });
    }
};
