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
        Schema::create('user_location', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('latitude', 10, 8); // Ejemplo: -17.78629188
            $table->decimal('longitude', 11, 8); // Ejemplo: -63.18116966
            $table->decimal('accuracy', 8, 2)->nullable(); // Precisión en metros
            $table->decimal('altitude', 8, 2)->nullable(); // Altitud en metros
            $table->decimal('speed', 8, 2)->nullable(); // Velocidad en m/s
            $table->string('source')->default('mobile_app'); // Origen: mobile_app, web, manual
            $table->timestamp('recorded_at'); // Momento exacto en que se capturó la ubicación
            $table->timestamps();

            // Definir la clave foránea explícitamente
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');

            // Índices para consultas más rápidas
            $table->index(['user_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_location');
    }
};
