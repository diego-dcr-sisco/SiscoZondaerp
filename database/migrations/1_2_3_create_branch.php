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
        Schema::create('branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained('status')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('fiscal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('alt_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('address');
            $table->string('colony');
            $table->integer('zip_code');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('license_number')->nullable();
            $table->string('rfc')->nullable();
            $table->string('fiscal_regime')->nullable();
            $table->string('url')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
