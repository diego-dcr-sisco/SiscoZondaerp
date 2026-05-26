<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_product', function (Blueprint $table) {
            $table->index(['order_id', 'product_id', 'application_method_id', 'lot_id'], 'device_product_order_product_method_lot_idx');
        });

        Schema::table('device_pest', function (Blueprint $table) {
            $table->index(['order_id', 'device_id', 'pest_id'], 'device_pest_order_device_pest_idx');
        });

        Schema::table('device_states', function (Blueprint $table) {
            $table->index(['order_id', 'device_id'], 'device_states_order_device_idx');
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->index(['order_id', 'service_id', 'product_id', 'application_method_id', 'lot_id'], 'order_product_lookup_idx');
        });

        Schema::table('warehouse_movements', function (Blueprint $table) {
            $table->index(['warehouse_id', 'movement_id'], 'warehouse_movements_warehouse_movement_idx');
        });

        Schema::table('movement_products', function (Blueprint $table) {
            $table->index(['warehouse_movement_id', 'warehouse_id', 'movement_id', 'product_id'], 'movement_products_lookup_idx');
        });

        Schema::table('warehouse_order', function (Blueprint $table) {
            $table->index(['order_id', 'user_id', 'movement_id', 'product_id'], 'warehouse_order_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_order', function (Blueprint $table) {
            $table->dropIndex('warehouse_order_lookup_idx');
        });

        Schema::table('movement_products', function (Blueprint $table) {
            $table->dropIndex('movement_products_lookup_idx');
        });

        Schema::table('warehouse_movements', function (Blueprint $table) {
            $table->dropIndex('warehouse_movements_warehouse_movement_idx');
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->dropIndex('order_product_lookup_idx');
        });

        Schema::table('device_states', function (Blueprint $table) {
            $table->dropIndex('device_states_order_device_idx');
        });

        Schema::table('device_pest', function (Blueprint $table) {
            $table->dropIndex('device_pest_order_device_pest_idx');
        });

        Schema::table('device_product', function (Blueprint $table) {
            $table->dropIndex('device_product_order_product_method_lot_idx');
        });
    }
};
