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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('facturama_token')->nullable();
            $table->string('folio');
            $table->string('serie');
            $table->string('UUID')->nullable()->unique();

            //$table->enum('type', ['income', 'expense']);
            $table->foreignId('invoice_customer_id')->constrained('invoice_customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('order')->oninvoice_customersDelete('set null');
            $table->foreignId('contract_id')->nullable()->constrained('contract')->onDelete('set null');

            $table->date('issued_date');
            $table->date('due_date')->nullable();
            $table->string('expedition_place')->nullable();
            //$table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency')->default('MXN');
            $table->string('notes')->nullable();
            $table->integer('status')->nullable()->default(0);
            //$table->string('cfdi_use')->nullable(); // uso del CFDI ante el SAT
            $table->string('payment_form')->nullable(); // forma de pago ante el SAT
            $table->string('payment_method')->nullable();

            $table->date('stamped_date')->nullable();
            $table->string('cfdi_usage')->nullable();

            $table->string('receiver_name')->nullable();
            $table->string('receiver_rfc')->nullable();
            $table->string('receiver_cfdi_use')->nullable();
            $table->string('receiver_fiscal_regime')->nullable();
            $table->string('receiver_tax_zip_code')->nullable();

            $table->longText('cfdi_sign')->nullable();
            $table->longText('sat_cert_number')->nullable();
            $table->longText('sat_sign')->nullable();
            $table->string('rfc_prov_cert')->nullable();

            $table->longText('csd_serial_number')->nullable();

            // archivo XML generado
            $table->longText('xml_file')->nullable();
            // archivo PDF generado
            $table->longText('pdf_path')->nullable();

            // soft deletes
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
