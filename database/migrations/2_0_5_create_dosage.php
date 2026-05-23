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
        Schema::create('dosage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_id')->constrained('product_catalog')->onDelete('cascade');
            $table->foreignId('methd_id')->constrained('application_method')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('application_areas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosage');
    }
};
