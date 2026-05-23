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
        Schema::create('invoice_customers', function (Blueprint $table) {
            // Identificación básica
            $table->id();
            $table->enum('taxpayer', ['physical', 'moral']);
            $table->enum('type', ['client', 'worker']);
            $table->string('name');
            $table->string('social_reason')->nullable();
            $table->string('rfc');
            $table->string('phone');
            $table->string('email');

            // Campos específicos del trabajador (si aplica)
            $table->string('curp')->nullable();
            $table->string('nss')->nullable();
            $table->decimal('salary_daily', 10, 2)->nullable();
            $table->string('position_risk')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();

            // Información fiscal
            $table->string('tax_system');
            $table->string('cfdi_usage');

            // Dirección fiscal
            $table->string('zip_code');
            $table->string('state');
            $table->string('city');
            $table->string('address');

            // Términos comerciales
            $table->float('credit_limit')->nullable();
            $table->integer('credit_days')->nullable();
            $table->string('payment_method')->default('PUE');
            $table->string('payment_form')->default('01');

            // Estado y control
            $table->enum('status', ['facturable', 'moroso', 'no_facturable'])->default('no_facturable');
            $table->timestamps();

            // Índices recomendados
            $table->index('rfc');
            $table->index('email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_customers');
    }
};
