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
        if (!Schema::hasTable('product_treatments')) {
            Schema::create('product_treatments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('product_id')
                    ->constrained('product_catalog')
                    ->onDelete('cascade');

                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_treatments');
    }
};
