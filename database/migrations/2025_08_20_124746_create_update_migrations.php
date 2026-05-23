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
        /*Schema::table('user', function (Blueprint $table) {
            $table->string('session_token')->nullable()->unique()->after('email');
        });*/

        Schema::table('tracking', function (Blueprint $table) {
            // Agregar los campos para la relación polimórfica
            //$table->unsignedBigInteger('trackable_id')->nullable()->after('customer_id');
            //$table->string('trackable_type')->nullable()->after('trackable_id');

            // Índice compuesto para mejor performance en consultas polimórficas
            //$table->index(['trackable_id', DB::raw('trackable_type(100)')]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });

        Schema::table('tracking', function (Blueprint $table) {
            // Eliminar el índice primero
            $table->dropIndex(['trackable_id', 'trackable_type']);

            // Luego eliminar las columnas
            $table->dropColumn(['trackable_id', 'trackable_type']);
        });
    }
};
