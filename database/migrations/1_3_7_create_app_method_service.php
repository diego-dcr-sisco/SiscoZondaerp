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
        Schema::create('application_method_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service')->nullable();
            $table->foreignId('application_method_id')->constrained('application_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_method_service');
    }
};
