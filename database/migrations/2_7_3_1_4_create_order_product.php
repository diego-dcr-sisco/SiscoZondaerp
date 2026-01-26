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
        Schema::create('order_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: 'order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('service')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('product_catalog')->onDelete('cascade');
            $table->foreignId('metric_id')->nullable()->constrained('metric')->onDelete('cascade');
            $table->foreignId('application_method_id')->nullable()->constrained('application_method')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lot')->onDelete('cascade');
            $table->string('possible_lot')->nullable(); 
            $table->decimal('amount', 8, 3)->nullable();
            $table->string('dosage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product');
    }
};
