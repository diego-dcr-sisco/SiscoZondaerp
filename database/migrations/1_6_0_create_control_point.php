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
        Schema::create('control_point', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('product_catalog')->onDelete('cascade');
            $table->string('name');
            $table->string('color');
            $table->string('code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_point');
    }
};
