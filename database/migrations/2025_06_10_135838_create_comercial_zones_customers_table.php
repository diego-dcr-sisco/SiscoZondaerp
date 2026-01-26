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
        Schema::create('comercial_zone_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercial_zone_id')->constrained('comercial_zones')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            $table->timestamps();
        });
    }


    /* 
    public function up(): void
{
    // Primero crear la tabla sin foreign keys
    Schema::create('comercial_zone_customers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('comercial_zone_id');
        $table->unsignedBigInteger('customer_id');
        $table->timestamps();

        $table->unique(['comercial_zone_id', 'customer_id']);
    });

    // Luego añadir las foreign keys por separado
    Schema::table('comercial_zone_customers', function (Blueprint $table) {
        $table->foreign('comercial_zone_id')
              ->references('id')
              ->on('comercial_zones')
              ->onDelete('cascade');
    });

    Schema::table('comercial_zone_customers', function (Blueprint $table) {
        $table->foreign('customer_id')
              ->references('id')
              ->on('customer')
              ->onDelete('cascade');
    });
}
    */
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_zones');
    }
};
