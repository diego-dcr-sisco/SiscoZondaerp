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
        Schema::create('administrative', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('contract_type_id')->constrained('contract_type')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branch')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('company')->onDelete('cascade');
            $table->string('curp')->nullable();
            $table->string('rfc')->nullable();
            $table->string('nss')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('colony')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->date('birthdate')->nullable();
            $table->date('hiredate')->nullable();
            $table->double('salary')->nullable();
            $table->string('clabe')->nullable();
            $table->string('signature')->nullable();    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrative');
    }
};
