<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['service', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_credentials');
    }
};
