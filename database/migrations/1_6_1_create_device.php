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
        //Dispositivo - punto de control ya en mapa
        Schema::create('device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_control_point_id')->constrained('control_point')->onDelete('cascade');
            $table->foreignId('floorplan_id')->constrained('floorplans')->onDelete('cascade'); //plano
            $table->foreignId('application_area_id')->constrained('application_areas')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('product_catalog')->onDelete('cascade');
            $table->binary('qr')->nullable();
            $table->string('code')->nullable();
            $table->integer('itemnumber');
            $table->integer('nplan');
            $table->integer('version');
            $table->float('latitude', 10, 8)->nullable();
            $table->float('longitude', 10, 8)->nullable();
            $table->double('map_x', 15, 8)->nullable(); // Cambiado a DOUBLE
            $table->double('map_y', 15, 8)->nullable(); // Cambiado a DOUBLE
            $table->double('img_tamx', 15, 8)->nullable();
            $table->double('img_tamy', 15, 8)->nullable();
            $table->string('color');
            $table->integer('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device');
    }
};
