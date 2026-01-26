<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('facturama_token')->nullable();
            $table->string('uuid')->nullable();
            $table->string('folio');
            $table->string('expedition_place');
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('payroll_type'); // O = Ordinaria, E = Extraordinaria
            $table->string('cfdi_type')->default('N');
            $table->string('cfdi_use');

            //Employer
            $table->string('employer_registration')->nullable();

            // Employee
            $table->string('employee_name');
            $table->string('employee_rfc');
            $table->string('employee_curp');
            $table->string('employee_nss');
            $table->string('employee_zip_code');
            $table->decimal('employee_daily_salary', 10, 2);
            $table->string('employee_number')->nullable();


            $table->date('initial_payment_date');
            $table->date('final_payment_date');
            $table->string('month');
            $table->integer('days_paid');
            $table->integer('position_risk');
            $table->string('contract_type');
            $table->string('tax_regime');
            $table->string('frequency_payment');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('start_date_labor_relations')->nullable();

            // Totals
            $table->decimal('total_perceptions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('total_other_payments', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('status')->default('draft');
            $table->date('stamped_at')->nullable();
            
            $table->timestamps();
        });

        Schema::create('payroll_perceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('perception_type');
            $table->string('code');
            $table->string('description');
            $table->decimal('taxed_amount', 10, 2)->default(0);
            $table->decimal('exempt_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('deduction_type');
            $table->string('code');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('payroll_other_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('other_payment_type');
            $table->string('code');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->decimal('employment_subsidy_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_other_payments');
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('payroll_perceptions');
        Schema::dropIfExists('payrolls');
    }
};