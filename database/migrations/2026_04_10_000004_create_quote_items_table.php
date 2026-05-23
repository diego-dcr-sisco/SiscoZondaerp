<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quote')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->string('unit', 50)->default('servicio');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['quote_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};