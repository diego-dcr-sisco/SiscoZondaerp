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
        Schema::create('appearance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->default('#64b5f6');
            $table->string('secondary_color')->default('#b0bec5');
            $table->string('logo_path')->default('images/logo_reporte.png');
            $table->string('watermark_path')->default('images/watermark.png');
            $table->float('watermark_opacity')->default(0.1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appearance_settings');
    }
};