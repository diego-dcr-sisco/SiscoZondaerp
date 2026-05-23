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
        /*Schema::create('device_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('device_id')->constrained('device')->onDelete('cascade');
            $table->boolean('is_scanned')->default(false);
            $table->boolean('is_checked')->default(false);
            $table->longText('observations')->nullable();
            $table->longText('device_image')->nullable();
            $table->timestamps();
        });*/

        Schema::create('device_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')
                ->nullable()
                ->constrained('order');
            $table->unsignedBigInteger('device_id')
                ->nullable()
                ->constrained('device');
            $table->boolean('is_scanned')->default(false);
            $table->boolean('is_checked')->default(false);
            $table->longText('observations')->nullable();
            $table->longText('device_image')->nullable();
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_states');
    }
};
