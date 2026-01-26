<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'propagate_service';

    protected $fillable = [
        'id',
        'service_id',
        'details',
    ];

    public function up(): void
    {
        Schema::create('propagate_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('service')->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained('contract')->onDelete('cascade');
            $table->foreignId('setting_id')->nullable()->constrained('contract_service')->onDelete('cascade');
            $table->longText('text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propagate_service');
    }
};
