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
        Schema::create('rotation_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contract')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->integer('no_review');
            $table->text('important_text')->nullable();
            $table->text('notes')->nullable();
            $table->date('authorizated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotation_plan');
    }
};
