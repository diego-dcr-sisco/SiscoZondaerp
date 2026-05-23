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
        /*Schema::create('warehouse_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('warehouse_movement_id')->nullable()->constrained('warehouse_movements')->onDelete('cascade');
            $table->foreignId('movement_id')->constrained('movement_type')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_catalog')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lot')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });*/

        Schema::create('warehouse_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->unsignedBigInteger('warehouse_movement_id')->nullable()->constrained('warehouse_movements')->onDelete('cascade');
            $table->unsignedBigInteger('movement_id')->constrained('movement_type')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->constrained('order')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->constrained('user')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->constrained('product_catalog')->onDelete('cascade');
            $table->unsignedBigInteger('lot_id')->nullable()->constrained('lot')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_order');
    }
};
