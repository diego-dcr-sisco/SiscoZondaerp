<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_tracking_id')->constrained('daily_trackings')->cascadeOnDelete();
            $table->string('field');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->index(['daily_tracking_id', 'field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_tracking_logs');
    }
};
