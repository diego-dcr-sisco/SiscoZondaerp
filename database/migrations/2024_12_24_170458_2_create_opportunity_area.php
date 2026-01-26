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
        Schema::create('opportunity_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            $table->foreignId('application_area_id')->constrained('application_areas')->onDelete('cascade');
            $table->date('date');
            $table->text('opportunity');
            $table->text('recommendation')->nullable();
            $table->date('estimated_date');
            $table->integer('status');
            $table->integer('tracing');
            $table->longText('img_incidence')->nullable();
            $table->longText('img_conclusion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_area');
    }
};
