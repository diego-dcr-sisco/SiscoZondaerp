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

        Schema::create('invoice_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('product_key');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->float('amount');
            $table->float('tax_rate')->default(0.16);
            $table->string('tax_object')->default('02');
            $table->string('unit_code');
            $table->string('identification_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_concepts');
    }
};
