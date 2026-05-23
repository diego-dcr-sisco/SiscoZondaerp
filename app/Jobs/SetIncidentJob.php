<?php

namespace App\Jobs;

use App\Models\DevicePest;
use App\Models\DeviceProduct;
use App\Models\DeviceStates;
use App\Models\Order;
use App\Models\OrderIncidents;
use App\Models\Technician;
use App\Models\User;
use App\Services\ReportStockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetIncidentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(
        protected int $orderId,
        protected array $review,
        protected int $userId
    ) {
    }

    public function handle(ReportStockService $stockService): void
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($this->orderId);
            $user = User::findOrFail($this->userId);
            $deviceId = $this->review['device_id'];
            $questions = $this->review['questions'] ?? [];
            $pests = $this->review['pests'] ?? [];
            $products = $this->review['products'] ?? [];
            $observations = $this->review['states']['observations'] ?? null;
            $now = now();

            OrderIncidents::where('order_id', $order->id)
                ->where('device_id', $deviceId)
                ->delete();

            $incidentRows = collect($questions)->keyBy('id')->values()->map(function ($question) use ($order, $deviceId, $now) {
                return [
                    'order_id' => $order->id,
                    'question_id' => $question['id'],
                    'device_id' => $deviceId,
                    'answer' => $question['answer'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            if (!empty($incidentRows)) {
                OrderIncidents::insert($incidentRows);
            }

            DevicePest::where('order_id', $order->id)
                ->where('device_id', $deviceId)
                ->delete();

            $pestRows = collect($pests)->keyBy('id')->values()->map(function ($pest) use ($order, $deviceId, $now) {
                return [
                    'order_id' => $order->id,
                    'device_id' => $deviceId,
                    'pest_id' => $pest['id'],
                    'total' => $pest['quantity'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            if (!empty($pestRows)) {
                DevicePest::insert($pestRows);
            }

            DeviceProduct::where('order_id', $order->id)
                ->where('device_id', $deviceId)
                ->delete();

            $productRows = collect($products)->keyBy('id')->values()->map(function ($product) use ($order, $deviceId, $now) {
                return [
                    'order_id' => $order->id,
                    'device_id' => $deviceId,
                    'product_id' => $product['id'],
                    'application_method_id' => $product['application_method_id'],
                    'lot_id' => $product['lot_id'],
                    'quantity' => $product['quantity'] ?? 0,
                    'possible_lot' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            if (!empty($productRows)) {
                DeviceProduct::insert($productRows);
            }

            DeviceStates::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'device_id' => $deviceId,
                ],
                [
                    'is_checked' => true,
                    'observations' => $observations,
                ]
            );

            $serviceId = $order->services()->value('service.id');
            $groupedProducts = DeviceProduct::where('order_id', $order->id)
                ->select([
                    'product_id',
                    DB::raw('MIN(lot_id) as lot_id'),
                    DB::raw('MIN(application_method_id) as application_method_id'),
                    DB::raw('SUM(quantity) as amount'),
                ])
                ->groupBy('product_id')
                ->get();

            $productsData = [];
            foreach ($groupedProducts as $product) {
                $productsData[] = [
                    'product_id' => $product->product_id,
                    'service_id' => $serviceId,
                    'lot_id' => $product->lot_id,
                    'metric_id' => null,
                    'app_method_id' => $product->application_method_id,
                    'amount' => $product->amount,
                ];
            }

            $technician = $order->closed_by ? Technician::where('user_id', $order->closed_by)->first() : null;
            $stockService->sync($order, $productsData, $technician, $user);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error en SetIncidentJob - Order: {$this->orderId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
