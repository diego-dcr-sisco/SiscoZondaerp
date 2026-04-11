<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quote')->cascadeOnDelete();
            $table->string('role', 20);
            $table->string('name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('attention')->nullable();
            $table->string('rfc')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->unique(['quote_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_parties');
    }
};