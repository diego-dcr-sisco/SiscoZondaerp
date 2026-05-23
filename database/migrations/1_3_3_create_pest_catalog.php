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
        Schema::create('pest_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pest_category_id')->constrained('pest_category')->onDelete('cascade');
            $table->string('pest_code')->nullable();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pest_catalog');
    }
};
