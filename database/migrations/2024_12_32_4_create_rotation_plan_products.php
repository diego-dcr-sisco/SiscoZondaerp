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
        Schema::create('rotation_plan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_plan_id')->constrained('rotation_plan')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_catalog')->onDelete('cascade');
            $table->string('color');
            $table->json('months');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_plan_products');
    }
};
