<?php

/*use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_tracking', function (Blueprint $table) {
            $table->id();
            $table->morphs('model'); // Crea 'model_id' y 'model_type'
            $table->foreignId('service_id')->constrained('service')->onDelete('cascade');
            $table->date('tracking_date');
            $table->time('tracking_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tracking');
    }
};*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('service_tracking', function (Blueprint $table) {
        $table->id();
        // Limitar 'model_type' a 191 caracteres
        $table->string('model_type', 191); // Limitar el campo 'model_type' a 191 caracteres
        $table->unsignedBigInteger('model_id');
        $table->foreignId('service_id')->constrained('service')->onDelete('cascade');
        $table->date('tracking_date');
        $table->time('tracking_time')->nullable();
        $table->timestamps();

        // Crear índice compuesto para 'model_type' y 'model_id'
        $table->index(['model_type', 'model_id'], 'service_tracking_model_type_model_id_index');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tracking');
    }
};

