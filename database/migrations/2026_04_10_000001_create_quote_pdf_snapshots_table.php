<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_pdf_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quote')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('title')->default('Cotizacion de Servicios');
            $table->string('quote_no')->nullable();
            $table->string('currency', 10)->default('MXN');
            $table->date('issued_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('tax_percent', 8, 2)->default(16);
            $table->json('payload');
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['quote_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_pdf_snapshots');
    }
};
