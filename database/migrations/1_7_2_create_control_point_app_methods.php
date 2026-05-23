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
        Schema::create('control_point_app_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_method_id')->constrained('application_method')->onDelete('cascade');
            $table->foreignId('control_point_id')->constrained('control_point')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_points_app_methods');
    }
};
