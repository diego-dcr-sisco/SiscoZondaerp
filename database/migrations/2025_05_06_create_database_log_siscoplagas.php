<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('database_log_siscoplagas', function (Blueprint $table) {
            $table->id();

            // Usuario que realizó la acción
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->constrained('user')
                ->onDelete('set null');

            // Modelo afectado
            $table->string('model_type')
                ->comment('Clase del modelo (ej: App\Models\Customer)');

            $table->unsignedBigInteger('model_id')
                ->nullable()
                ->comment('ID del registro afectado');

            // Comando SQL ejecutado
            $table->longText('sql_query')
                ->comment('Consulta SQL completa ejecutada');

            // Evento personalizado
            $table->string('event')
                ->comment('Tipo de evento: created, updated, deleted, etc.');

            // Timestamps
            $table->timestamps();

            // Índices para mejor performance
            $table->index(['model_type', 'model_id']);
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_log');
    }

};
