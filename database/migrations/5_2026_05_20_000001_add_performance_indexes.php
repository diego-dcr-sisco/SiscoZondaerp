<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->index(['status_id', 'programmed_date'], 'order_status_programmed_idx');
            $table->index(['customer_id', 'status_id'], 'order_customer_status_idx');
            $table->index(['programmed_date', 'start_time'], 'order_programmed_start_idx');
        });

        Schema::table('order_service', function (Blueprint $table) {
            $table->index(['service_id', 'order_id'], 'order_service_service_order_idx');
        });

        Schema::table('order_technician', function (Blueprint $table) {
            $table->index(['technician_id', 'order_id'], 'order_technician_technician_order_idx');
        });

        Schema::table('order_incidents', function (Blueprint $table) {
            $table->index(['order_id', 'device_id', 'question_id'], 'order_incidents_order_device_question_idx');
        });

        Schema::table('device_product', function (Blueprint $table) {
            $table->index(['order_id', 'device_id'], 'device_product_order_device_idx');
        });
    }

    public function down(): void
    {
        Schema::table('device_product', function (Blueprint $table) {
            $table->dropIndex('device_product_order_device_idx');
        });

        Schema::table('order_incidents', function (Blueprint $table) {
            $table->dropIndex('order_incidents_order_device_question_idx');
        });

        Schema::table('order_technician', function (Blueprint $table) {
            $table->dropIndex('order_technician_technician_order_idx');
        });

        Schema::table('order_service', function (Blueprint $table) {
            $table->dropIndex('order_service_service_order_idx');
        });

        Schema::table('order', function (Blueprint $table) {
            $table->dropIndex('order_programmed_start_idx');
            $table->dropIndex('order_customer_status_idx');
            $table->dropIndex('order_status_programmed_idx');
        });
    }
};
