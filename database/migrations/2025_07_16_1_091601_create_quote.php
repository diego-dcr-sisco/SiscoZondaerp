<?php

use App\Enums\QuoteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\QuotePriority;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('service')  // ← 'service' en singular
                ->onDelete('cascade');
            $table->unsignedBigInteger('model_id'); // ID del modelo relacionado
            $table->string('model_type'); // Clase del modelo (Customer o Lead)
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->float('value', 15, 2)->nullable();
            $table->string('priority')->default(QuotePriority::LOW->value);
            $table->string('status')->default(QuoteStatus::DRAFT->value);
            $table->integer('probability')->nullable();
            $table->text('comments')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();

            $table->index(['model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote');
    }
};
