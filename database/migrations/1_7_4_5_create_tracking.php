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
        Schema::create('tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trackable_id'); // ID del modelo relacionado
            $table->string('trackable_type'); // Clase del modelo (Customer o Lead)
            $table->foreignId('service_id')->constrained('service')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('order')->onDelete('cascade');
            $table->date('next_date');
            $table->json('range')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['trackable_id', 'trackable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking');
    }
};
