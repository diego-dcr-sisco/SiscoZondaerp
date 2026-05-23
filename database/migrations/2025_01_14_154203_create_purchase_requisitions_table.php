<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreatePurchaseRequisitionsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade')->nullable();
            $table->string('folio')->unique();
            $table->enum('status', ['Pendiente', 'Cotizada', 'Aprobada', 'Rechazada'])->default('Pendiente');
            $table->date('request_date');
            $table->string('observations')->nullable();
            $table->date('approval_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_requisitions');
    }
}
