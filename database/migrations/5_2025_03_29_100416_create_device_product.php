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
        Schema::create('device_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('device')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_catalog')->onDelete('cascade');
            $table->foreignId('application_method_id')->nullable()->constrained('application_method')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lot')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('possible_lot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_product');
    }
};
