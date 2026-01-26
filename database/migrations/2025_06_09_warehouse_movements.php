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
        /*Schema::create('warehouse_movements', function (Blueprint $table) {
            // Modificado el 7 de julio de 2025, se agrega el campo de imagen y firmas 
            $table->id(); 
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('movement_id')->constrained('movement_type')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->longText('warehouse_signature')->nullable(); 
            $table->longText('technician_signature')->nullable(); 
            $table->string('image_path')->nullable(); 
            $table->date('date'); 
            $table->time('time')->nullable(); 
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);         
            $table->timestamps();
        });*/

        Schema::create('warehouse_movements', function (Blueprint $table) {
            // Modificado el 7 de julio de 2025, se agrega el campo de imagen y firmas 
            $table->id();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->foreignId('movement_id')->constrained('movement_type')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->foreign('user_id')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');
            $table->longText('warehouse_signature')->nullable();
            $table->longText('technician_signature')->nullable();
            $table->string('image_path')->nullable();
            $table->date('date');
            $table->time('time')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_movements');
    }
};
