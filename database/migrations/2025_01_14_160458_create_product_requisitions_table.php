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
        Schema::create('product_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->enum('type', ['directo', 'indirecto']);
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->onDelete('cascade');
            $table->float('quantity');
            $table->string('unit');
            $table->string('description');
            $table->foreignId('supplier1_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->decimal('supplier1_cost', 10, 2)->default(0);
            $table->foreignId('supplier2_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->decimal('supplier2_cost', 10, 2)->default(0);
            $table->foreignId('approved_supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->float('purchase_value')->nullable()->default(0);
            $table->float('commercial_value')->nullable()->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requisitions');
    }
};
