<?php

namespace App\Services;

use App\Models\MovementProduct;
use App\Models\OrderProduct;
use App\Models\ProductCatalog;
use App\Models\Warehouse;
use App\Models\WarehouseMovement;
use App\Models\WarehouseOrder;

class ReportStockService
{
    public function sync($order, array $productsData, $technician, $user): void
    {
        $updatedProducts = [];
        $updatedLots = [];
        $updatedOrderProducts = [];
        $updatedWarehouseOrders = [];

        $warehouse = $technician ? Warehouse::where('technician_id', $technician->id)->first() : null;
        $productIds = collect($productsData)->pluck('product_id')->filter()->unique()->values();
        $products = ProductCatalog::whereIn('id', $productIds)->get()->keyBy('id');
        $warehouseMovement = null;

        if ($warehouse && count($productsData) > 0) {
            $warehouseMovement = WarehouseMovement::updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'destination_warehouse_id' => null,
                    'movement_id' => 8,
                    'observations' => 'Movimiento realizado en la order #' . $order->folio . ' | ID: ' . $order->id,
                ],
                [
                    'user_id' => $user->id,
                    'date' => now(),
                    'time' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($productsData as $productData) {
            $multiplier = 1;
            $product = $products->get($productData['product_id']);

            if (!$product) {
                continue;
            }

            $orderProduct = OrderProduct::updateOrCreate([
                'order_id' => $order->id,
                'service_id' => $productData['service_id'],
                'product_id' => $product->id,
                'application_method_id' => $productData['app_method_id'] ?? null,
                'lot_id' => $productData['lot_id'] ?? null,
            ], [
                'metric_id' => $productData['metric_id'] ?? $product->metric_id ?? null,
                'amount' => $productData['amount'],
                'dosage' => $productData['dosage'] ?? $product->dosage ?? null,
            ]);

            $updatedOrderProducts[] = $orderProduct->id;

            if ($product->id == 4 && ($productData['metric_id'] ?? null) == 5) {
                $multiplier = 1000;
            }

            if ($product->id == 2 && ($productData['metric_id'] ?? null) == 3) {
                $multiplier = 1000;
            }

            if ($product->id == 1 && ($productData['metric_id'] ?? null) == 2) {
                $multiplier = 1000;
            }

            if ($warehouse && $warehouseMovement) {
                $movementProduct = MovementProduct::updateOrCreate([
                    'warehouse_movement_id' => $warehouseMovement->id,
                    'movement_id' => 8,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ], [
                    'lot_id' => $productData['lot_id'] ?? null,
                    'amount' => $productData['amount'] * $multiplier,
                ]);

                $updatedProducts[] = $movementProduct->product_id;
                $updatedLots[] = $movementProduct->lot_id;
            }

            $warehouseOrder = WarehouseOrder::updateOrCreate([
                'movement_id' => 8,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'product_id' => $productData['product_id'],
            ], [
                'warehouse_id' => $warehouse->id ?? null,
                'warehouse_movement_id' => $warehouseMovement->id ?? null,
                'lot_id' => $productData['lot_id'] ?? null,
                'amount' => $productData['amount'] * $multiplier,
            ]);

            $updatedWarehouseOrders[] = $warehouseOrder->id;
        }

        if ($warehouseMovement && (count($updatedProducts) > 0 || count($updatedLots) > 0)) {
            MovementProduct::where('warehouse_movement_id', $warehouseMovement->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('movement_id', 8)
                ->whereNotIn('product_id', $updatedProducts)
                ->whereNotIn('lot_id', $updatedLots)
                ->delete();
        }

        OrderProduct::where('order_id', $order->id)->whereNotIn('id', $updatedOrderProducts)->delete();
        WarehouseOrder::where('order_id', $order->id)->whereNotIn('id', $updatedWarehouseOrders)->delete();
    }
}
