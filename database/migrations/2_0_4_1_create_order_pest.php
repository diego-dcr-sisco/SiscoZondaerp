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
        Schema::create('order_pest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('service')->onDelete('cascade');
            $table->foreignId('pest_id')->constrained('pest_catalog')->onDelete('cascade');
            $table->integer('total');
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('order_pest');
    }
};
