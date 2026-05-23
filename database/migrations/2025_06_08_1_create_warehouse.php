<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*Schema::create('warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branch')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('technician')->onDelete('cascade');
            $table->string('name');
            $table->text('observations')->nullable(); 
            $table->boolean('allow_material_receipts');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_matrix')->default(false);
            $table->timestamps();
        });*/

        Schema::create('warehouse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->foreign('branch_id')
              ->references('id')
              ->on('branch')
              ->onDelete('cascade');
            $table->unsignedBigInteger('technician_id')->nullable();

            $table->foreign('technician_id')
              ->references('id')
              ->on('technician')
              ->onDelete('cascade');
            $table->string('name');
            $table->text('observations')->nullable(); 
            $table->boolean('allow_material_receipts');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_matrix')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse');
    }
};
