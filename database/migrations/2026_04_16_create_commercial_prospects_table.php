<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commercial_prospects', function (Blueprint $table) {
            $table->id();
            $table->string('commercial_name');
            $table->date('date')->nullable();
            $table->string('commerce_type')->nullable();
            $table->string('quotation_status')->nullable();
            $table->text('close_reason')->nullable();
            $table->string('contact_method')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->timestamps();

            $table->index(['commercial_name']);
            $table->index(['date']);
            $table->index(['quotation_status']);
            $table->fullText(['commercial_name', 'commerce_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_prospects');
    }
};
