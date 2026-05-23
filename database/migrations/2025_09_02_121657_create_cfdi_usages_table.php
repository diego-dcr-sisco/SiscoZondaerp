<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cfdi_usages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique(); // G01, G02, etc.
            $table->string('description');
            $table->enum('type', ['G', 'I', 'D', 'S', 'CP', 'CN']); // Tipo de uso
            $table->enum('applicable_to', ['physical', 'moral', 'both'])->default('both');
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('type');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfdi_usages');
    }
};
