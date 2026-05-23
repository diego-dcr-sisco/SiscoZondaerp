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
        Schema::create('service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefix')->constrained('service_prefix')->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained('service_type')->onDelete('cascade');
            $table->foreignId('business_line_id')->constrained('line_business')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('service_status')->onDelete('cascade');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('time')->nullable();
            $table->string('time_unit')->nullable();
            $table->float('cost')->nullable();
            $table->boolean('has_pests')->default(false);
            $table->boolean('has_application_methods')->default(false);     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service');
    }
};
