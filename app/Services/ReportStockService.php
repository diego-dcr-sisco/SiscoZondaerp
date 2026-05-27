<?php

namespace App\Services;

use App\Models\MovementProduct;
use App\Models\OrderProduct;
use App\Models\ProductCatalog;
use App\Models\Warehouse;
use App\Models\WarehouseMovement;
use App\Models\WarehouseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportStockService
{
    public function sync($order, array $productsData, $technician, $user): void
    {
        $updatedOrderProducts = [];

        $productIds = collect($productsData)->pluck('product_id')->filter()->unique()->values();
        $products = ProductCatalog::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productsData as $productData) {
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
        }

        OrderProduct::where('order_id', $order->id)->whereNotIn('id', $updatedOrderProducts)->delete();

        try {
            $this->syncWarehouseStock($order, $productsData, $technician, $user, $products);
        } catch (\Throwable $e) {
            Log::warning('No se pudo sincronizar almacén desde reporte, el reporte se guardó sin afectar stock.', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function syncWarehouseStock($order, array $productsData, $technician, $user, $products): void
    {
        $updatedProducts = [];
        $updatedLots = [];
        $updatedWarehouseOrders = [];
        $warehouse = $technician ? Warehouse::where('technician_id', $technician->id)->first() : null;
        $movementTypeExists = DB::table('movement_type')->where('id', 8)->exists();
        $warehouseMovement = null;

        if (!$movementTypeExists) {
            WarehouseOrder::where('order_id', $order->id)->delete();

            Log::warning('No se sincronizó almacén desde reporte porque no existe movement_type id 8.', [
                'order_id' => $order->id,
            ]);

            return;
        }

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

        WarehouseOrder::where('order_id', $order->id)->whereNotIn('id', $updatedWarehouseOrders)->delete();
    }
}
