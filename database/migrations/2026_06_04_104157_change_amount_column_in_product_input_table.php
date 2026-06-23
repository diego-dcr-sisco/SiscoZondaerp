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
        Schema::table('product_input', function (Blueprint $table) {
            $table->decimal('amount', 12, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_input', function (Blueprint $table) {
            // Revierte el cambio a entero si se hace rollback
            $table->integer('amount')->change();
        });
    }
};
