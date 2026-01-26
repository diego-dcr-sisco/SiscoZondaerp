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
        Schema::create('product_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('product_catalog')->onDelete('cascade'); /*id producto */
            $table->char('measure_type', 1)->nullable();/* tipo de medida(compra, almacenaje y venta) */
            $table->char('measure', 1)->nullable(); /*medida (peso, volumen, longitud, superficie y contable) */
            $table->char('measure_unit', 2)->nullable(); /*unidad de medida (g, kg, mg, cc, l,... etc) */
            $table->integer('unit_number')->nullable();/*la unidad que elegimos a cuanto corresponde 1, 3 ,2 .. etc */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
