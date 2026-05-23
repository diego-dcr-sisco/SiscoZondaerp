<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotation_plan_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_plan_id')->constrained('rotation_plan')->onDelete('cascade');
            $table->integer('no_review');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_plan_changes');
    }
};
