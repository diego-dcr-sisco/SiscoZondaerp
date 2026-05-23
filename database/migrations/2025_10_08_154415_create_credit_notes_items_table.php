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
        Schema::create('credit_notes_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->string('name');
            $table->string('product_code'); //product_key
            $table->string('unit')->nullable();
            $table->string('unit_code')->nullable();
            $table->string('description');
            $table->string('identification_number')->nullable();
            $table->float('unit_price'); // amount
            $table->float('subtotal');
            $table->float('discount_rate')->nullable();

            //Tax data
            $table->string('tax_name')->default('IVA');
            $table->float('tax_rate')->default(0.16);
            $table->string('tax_object')->default('02');
            $table->float('tax_total')->default(0.16);
            $table->float('tax_base')->nullable();
            $table->boolean('tax_is_retention')->default(false);
            $table->boolean('tax_is_federal_tax')->default(true);

            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes_items');
    }
};
