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
        /*Schema::create('movement_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_movement_id')->constrained('warehouse_movements')->onDelete('cascade');
            $table->foreignId('movement_id')->constrained('movement_type')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_catalog')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lot')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });*/


        Schema::create('movement_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_movement_id');
            $table->unsignedBigInteger('movement_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('lot_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            // Llaves foráneas con el formato específico
            $table->foreign('warehouse_movement_id')
                ->references('id')
                ->on('warehouse_movements')
                ->onDelete('cascade');

            $table->foreign('movement_id')
                ->references('id')
                ->on('movement_type')
                ->onDelete('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouse')
                ->onDelete('cascade');

            /*$table->foreign('product_id')
                ->references('id')
                ->on('product_catalog')
                ->onDelete('cascade');*/

            $table->foreign('lot_id')
                ->references('id')
                ->on('lot')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_products');
    }
};
