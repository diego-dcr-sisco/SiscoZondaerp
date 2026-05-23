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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('cfdi_type')->default('P');
            $table->string('facturama_token')->nullable();
            $table->string('UUID')->nullable();
            $table->string('folio');
            $table->string('serie');
            $table->string('expedition_place');
            $table->string('receiver_name')->nullable();
            $table->string('receiver_rfc')->nullable();
            $table->string('receiver_cfdi_use')->nullable();
            $table->string('receiver_fiscal_regime')->nullable();
            $table->string('receiver_tax_zip_code')->nullable();

            $table->integer('status');
            $table->date('stamped_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
