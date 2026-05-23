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
        Schema::table('tracking', function (Blueprint $table) {
            // Agregar la columna user_id como nullable y con clave foránea
            $table->foreignId('user_id')
                  ->nullable()
                  //->after('customer_id') // Opcional: especifica la posición de la columna
                  ->constrained('user')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            // Eliminar la clave foránea y la columna
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};