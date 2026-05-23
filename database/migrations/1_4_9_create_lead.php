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
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_category_id')->nullable()->constrained('company_category')->onDelete('cascade');
            $table->foreignId('administrative_id')->nullable()->constrained('user')->onDelete('cascade');
            $table->foreignId('service_type_id')->nullable()->constrained('service_type')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branch')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('cascade');
            $table->enum('contact_medium', ['whatsapp', 'sms', 'call', 'email', 'flyer']); // ALTER TABLE lead ADD COLUMN contact_medium ENUM('whatsapp', 'sms', 'call', 'email', 'flyer');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->integer('zip_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('status')->nullable();
            $table->string('city')->nullable(); 
            $table->string('state')->nullable();
            $table->string('map_location_url', 1000)->nullable();
            $table->string('reason', 1024)->nullable();
            $table->date('tracking_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
