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
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrative_id')->nullable()->constrained('administrative')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            $table->foreignId('status_id')->constrained('order_status')->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained('contract')->onDelete('cascade');
            $table->foreignId('setting_id')->nullable()->constrained('contract_service')->onDelete('cascade');
            $table->string('folio')->unique()->nullable();
            //$table->string('code')->unique()->nullable();   // Tracking (TRK) o Contrato (MIP)
            $table->string('customer_observations')->nullable();
            $table->string('technical_observations')->nullable();
            $table->string('recommendations', 1000)->nullable();
            $table->string('comments')->nullable();
            $table->longText('customer_signature')->nullable();
            $table->string('customer_sig_path')->nullable();
            $table->string('signature_name')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->date('programmed_date');
            $table->date('completed_date')->nullable();
            $table->longText('execution')->nullable();
            $table->longText('areas')->nullable();
            $table->longText('additional_comments')->nullable();
            $table->float('price')->nullable();
            $table->longText('notes')->nullable();
            $table->integer('closed_by')->nullable();
            // Nuevos campos para tracking -------------------------------
            $table->integer('synchronized_by')->nullable();
            $table->timestamp('synchronized_at')->nullable();
            // -----------------------------------------------------------
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
