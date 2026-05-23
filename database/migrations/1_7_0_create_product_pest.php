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
        Schema::create('product_pest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('product_catalog')->onDelete('cascade'); // Categorias de toxicidad
            $table->foreignId('pest_id')->nullable()->constrained('pest_catalog')->onDelete('cascade'); // Categorias de toxicidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pest');
    }
};
