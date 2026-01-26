<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('quote_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quote')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('user'); // Quién hizo el cambio
            $table->string('changed_column'); // Ej: 'priority', 'status', 'value'
            $table->text('old_value')->nullable(); // Valor anterior
            $table->text('new_value'); // Nuevo valor
            $table->string('operation_type')->nullable()->comment('created, updated');
            $table->timestamps(); // Fecha del cambio
        });
    }

    public function down()
    {
        Schema::dropIfExists('quote_histories');
    }
};