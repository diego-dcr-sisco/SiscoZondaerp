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
        Schema::create('payments_related_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_item_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('cfdi_uuid')->nullable();
            $table->string('partiality_number')->nullable();
            $table->string('folio');
            $table->string('serie');
            $table->string('payment_method')->nullable();
            $table->float('previous_balance_amount'); // amount
            $table->float('amount_paid');
            $table->float('imp_saldo_insoluto')->nullable();

            //Tax data
            $table->string('tax_object')->default('02');
            $table->string('tax_name')->default('IVA');
            $table->float('tax_rate')->default(0.16);
            $table->float('tax_total')->default(0.16);
            $table->float('tax_base')->nullable();
            $table->boolean('tax_is_retention')->default(false);
            $table->boolean('tax_is_federal_tax')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_related_documents');
    }
};
