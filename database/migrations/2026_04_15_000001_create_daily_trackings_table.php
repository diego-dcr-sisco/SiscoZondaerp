<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->string('customer_type');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_method');
            $table->string('status')->default('survey');
            $table->string('service_type');
            $table->boolean('responded')->default(false);
            $table->string('quoted')->default('pending');
            $table->string('closed')->default('pending');
            $table->boolean('has_not_coverage')->default(false);
            $table->decimal('quoted_amount', 12, 2)->nullable();
            $table->decimal('billed_amount', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('invoice')->default('not_applicable');
            $table->date('service_date')->nullable();
            $table->date('quote_sent_date')->nullable();
            $table->date('close_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->time('service_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->unsignedBigInteger('status_updated_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'service_type']);
            $table->index(['customer_name']);
            $table->index(['service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_trackings');
    }
};
