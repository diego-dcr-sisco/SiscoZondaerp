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
        Schema::create('evidence_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('order')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('service')->onDelete('cascade');
            $table->json('evidence_data'); // Almacena el JSON con toda la información de la imagen
            $table->string('filename');
            $table->string('filetype');
            $table->text('description');
            $table->enum('area', ['servicio', 'notas', 'recomendaciones', 'evidencias']);
            $table->timestamps();

            // Índices para mejor performance
            $table->index(['order_id', 'service_id']);
            $table->index('area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_photos');
    }
};