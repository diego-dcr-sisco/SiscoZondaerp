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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_category_id')->nullable()->constrained('company_category')->onDelete('cascade')->nullable();
            $table->foreignId('administrative_id')->nullable()->constrained('user')->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained('service_type')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branch')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('cascade');
            $table->enum('contact_medium', ['whatsapp', 'sms', 'call', 'email', 'flyer']);
            $table->foreignId('tax_regime_id')->nullable()->constrained('tax_regime')->onDelete('cascade');            
            $table->integer('general_sedes')->nullable()->default(0);
            
            // Datos generales
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('code')->unique()->nullable();
            $table->integer('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->nullable();/* Activo(1) o inactivo(0) */
            $table->string('map_location_url', 500)->nullable();

            // Datos fiscales
            $table->string('tax_name')->nullable();//razon social
            $table->string('rfc', 13)->nullable(); 

            // Datos de horario
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Datos de la zona de alcance
            $table->string('business_area_branch')->nullable(); // Sucursal de una zona comercial (Branch)
            $table->float('meters')->nullable();
            $table->integer('unit')->nullable();

            // Datos para portal
            $table->string('url')->nullable();
            $table->string('portal_email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();

            $table->integer('blueprints')->nullable();
            $table->integer('print_doc')->nullable();
            $table->integer('validate_certificate')->nullable();
                        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
