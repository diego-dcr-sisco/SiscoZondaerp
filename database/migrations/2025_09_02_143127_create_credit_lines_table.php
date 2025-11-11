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
        Schema::create('credit_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            $table->decimal('limit_amount', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->date('cutoff_date')->nullable();
            $table->date('payment_deadline')->nullable();
            $table->foreignId('updated_by')->constrained('user')->onDelete('cascade');
            $table->string('notes')->nullable();
            $table->enum('status', ['inactive', 'active'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_lines');
    }
};
