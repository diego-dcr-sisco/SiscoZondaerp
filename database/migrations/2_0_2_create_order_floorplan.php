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
        Schema::create('order_floorplan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floorplan_id')->constrained('floorplans')->onDelete('cascade'); /*id producto */
            $table->foreignId('version_id')->constrained('floorplan_version')->onDelete('cascade');
            $table->integer('checked')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_floorplan');
    }
};
