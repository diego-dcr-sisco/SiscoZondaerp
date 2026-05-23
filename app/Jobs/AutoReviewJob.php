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

class AutoReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(
        protected int $orderId,
        protected array $autoreviewData,
        protected int $userId
    ) {
    }

    public function handle(ReportStockService $stockService): void
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($this->orderId);
            $user = User::findOrFail($this->userId);
            $productsData = [];

            foreach (($this->autoreviewData['control_points'] ?? []) as $controlPoint) {
                $answers = $controlPoint['answers'] ?? [];
                $products = $controlPoint['products'] ?? [];
                $pests = $controlPoint['pests'] ?? [];
                $devices = $controlPoint['devices'] ?? [];
                $observations = $controlPoint['observations'] ?? '';
                $clear = $controlPoint['clear'] ?? [];
                $questions = $controlPoint['questions'] ?? [];

                if (($clear['questions'] ?? false) && count($devices) > 0) {
                    OrderIncidents::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                if (($clear['products'] ?? false) && count($devices) > 0) {
                    DeviceProduct::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                if (($clear['pests'] ?? false) && count($devices) > 0) {
                    DevicePest::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                foreach ($devices as $deviceId) {
                    $updatedIncidents = [];
                    $updatedQuestions = [];

                    foreach ($answers as $questionId => $answer) {
                        if (!in_array($questionId, $questions)) {
                            continue;
                        }

                        $incident = OrderIncidents::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'question_id' => $questionId,
                                'device_id' => $deviceId,
                            ],
                            ['answer' => $answer]
                        );

                        $updatedQuestions[] = $questionId;
                        $updatedIncidents[] = $incident->id;
                    }

                    foreach ($products as $product) {
                        DeviceProduct::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'device_id' => $deviceId,
                                'product_id' => $product['product_id'],
                            ],
                            [
                                'application_method_id' => ($product['application_method_id'] ?? '') !== '' ? $product['application_method_id'] : null,
                                'lot_id' => ($product['lot_id'] ?? '') !== '' ? $product['lot_id'] : null,
                                'quantity' => ($product['amount'] ?? '') !== '' ? $product['amount'] : 0,
                                'possible_lot' => null,
                            ]
                        );
                    }

                    foreach ($pests as $pest) {
                        DevicePest::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'device_id' => $deviceId,
                                'pest_id' => $pest['pest_id'],
                            ],
                            ['total' => $pest['count']]
                        );
                    }

                    $state = DeviceStates::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'device_id' => $deviceId,
                        ],
                        ['is_checked' => true]
                    );

                    if ($clear['observations'] ?? false) {
                        $state->observations = null;
                        $state->save();
                    } elseif ($observations !== '') {
                        $state->observations = $observations;
                        $state->save();
                    }

                    if (!empty($updatedQuestions)) {
                        OrderIncidents::where('order_id', $order->id)
                            ->where('device_id', $deviceId)
                            ->whereIn('question_id', $updatedQuestions)
                            ->whereNotIn('id', $updatedIncidents)
                            ->delete();
                    }
                }
            }

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
            Log::error("Error en AutoReviewJob - Order: {$this->orderId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
