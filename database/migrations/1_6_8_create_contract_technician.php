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
        Schema::create('contract_technician', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contract')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('technician')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_technician');
    }
};
