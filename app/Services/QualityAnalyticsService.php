<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Device;
use App\Models\OrderIncidents;
use App\Models\OrderTechnician;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualityAnalyticsService
{
    // Constants for order status
    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_FINISHED = 3;
    const STATUS_VERIFIED = 4;
    const STATUS_APPROVED = 5;
    const STATUS_CANCELED = 6;
    
    // Constants for consumption levels
    const CONSUMPTION_LEVELS = [
        'Nulo' => 0,
        'Bajo' => 0.25,
        'Medio' => 0.5,
        'Alto' => 0.75,
        'Total' => 1,
    ];
    
    // Question ID for device consumption (previously magic number 13)
    const DEVICE_CONSUMPTION_QUESTION_ID = 13;

    /**
     * Get comprehensive analytics data for a customer
     */
    public function getAnalyticsData(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        return [
            'customer' => $customer,
            'totalOrders' => $this->getTotalOrdersCount($customerId),
            'lastModifiedOrders' => $this->getLastModifiedOrders($customerId),
            'approvedOrders' => $this->getApprovedOrders($customerId),
            'services' => $this->getServices($customerId),
            'serviceStats' => $this->getServiceStatistics($customerId),
            'monthlyOrders' => $this->getMonthlyOrderStats($customerId),
            'technicianStats' => $this->getTechnicianStats($customerId),
            'deviceTypes' => $this->getCustomerDeviceTypes($customerId),
            'ConsumptionData' => $this->getDeviceConsumptionData($customerId, $startDate, $endDate),
            'devices' => $this->getCustomerDevices($customerId),
            'deviceId' => null,
            'weeks' => $this->generateWeekRange($startDate, $endDate),
            'table' => $this->buildConsumptionTable($customerId, $startDate, $endDate),
            'dateRange' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'ordersByStatus' => $this->getTotalOrdersByStatus($customerId)
        ];
    }

    /**
     * Get device consumption table data with filters
     */
    public function getDeviceConsumptionTableData(int $customerId, ?string $dateRange = null, ?int $serviceId = null): array
    {
        $dateRange = $dateRange ?? now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y');
        [$startDate, $endDate] = $this->parseDateRange($dateRange);
        
        return [
            'customer' => Customer::findOrFail($customerId),
            'services' => $this->getServices($customerId),
            'serviceId' => $serviceId,
            'orders' => $this->getFilteredOrders($customerId, $serviceId),
            'ordersByStatus' => $this->getTotalOrdersByStatus($customerId),
            'devices' => $this->getDevicesFromOrders($this->getFilteredOrders($customerId, $serviceId)),
            'consumptionData' => $this->getDeviceConsumptionData($customerId, $startDate, $endDate, $serviceId),
            'dateRange' => $dateRange,
            'totalOrders' => $this->getTotalOrdersCount($customerId),
            'lastModifiedOrders' => $this->getLastModifiedOrders($customerId),
            'approvedOrders' => $this->getApprovedOrders($customerId),
            'serviceStats' => $this->getServiceStatistics($customerId),
            'monthlyOrders' => $this->getMonthlyOrderStats($customerId),
            'technicianStats' => $this->getTechnicianStats($customerId)
        ];
    }

    /**
     * Get total orders count for a customer
     */
    public function getTotalOrdersCount(int $customerId): int
    {
        return Order::where('customer_id', $customerId)->count();
    }

    /**
     * Get services for a customer with optimized query
     */
    public function getServices(int $customerId)
    {
        return DB::table('order')
            ->join('order_service', 'order.id', '=', 'order_service.order_id')
            ->join('service', 'order_service.service_id', '=', 'service.id')
            ->where('order.customer_id', $customerId)
            ->select('service.id', 'service.name')
            ->distinct()
            ->get();
    }

    /**
     * Get last modified orders with eager loading
     */
    public function getLastModifiedOrders(int $customerId, int $limit = 5)
    {
        return Order::where('customer_id', $customerId)
        ->with(['services' => function($query) {
            $query->select('service.id', 'service.name');
        }])
        ->orderBy('updated_at', 'desc')
        ->limit($limit)
        ->get();
        // return Order::where('customer_id', $customerId)
        //     ->with(['services:id,name'])
        //     ->orderBy('updated_at', 'desc')
        //     ->limit($limit)
        //     ->get();
    }

    /**
     * Get approved orders with optimized relationships
     */
    public function getApprovedOrders(int $customerId)
    {
        return Order::where('customer_id', $customerId)
            ->where('status_id', self::STATUS_APPROVED)
            ->with(['services:id,name', 'technicians.user:id,name'])
            ->orderBy('programmed_date', 'desc')
            ->get();
    }

    /**
     * Get service statistics with optimized query
     */
    public function getServiceStatistics(int $customerId)
    {
        return DB::table('order')
            ->join('order_service', 'order.id', '=', 'order_service.order_id')
            ->join('service', 'order_service.service_id', '=', 'service.id')
            ->where('order.customer_id', $customerId)
            ->where('order.status_id', self::STATUS_APPROVED)
            ->select('service.name', DB::raw('count(*) as total'))
            ->groupBy('service.name')
            ->get();
    }

    /**
     * Get monthly order statistics
     */
    public function getMonthlyOrderStats(int $customerId, int $limit = 12)
    {
        return Order::where('customer_id', $customerId)
            ->where('status_id', self::STATUS_APPROVED)
            ->select(
                DB::raw('MONTH(programmed_date) as month'),
                DB::raw('YEAR(programmed_date) as year'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get technician statistics with optimized query
     */
    public function getTechnicianStats(int $customerId)
    {
        return DB::table('order_technician')
            ->join('order', 'order_technician.order_id', '=', 'order.id')
            ->join('technician', 'order_technician.technician_id', '=', 'technician.id')
            ->join('user', 'technician.user_id', '=', 'user.id')
            ->where('order.customer_id', $customerId)
            ->where('order.status_id', self::STATUS_APPROVED)
            ->select('user.name', DB::raw('count(*) as total'))
            ->groupBy('user.name')
            ->get();
    }

    /**
     * Get customer devices with optional filtering
     */
    public function getCustomerDevices(int $customerId, ?int $deviceId = null)
    {
        $query = Device::whereHas('floorplan', function($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })
            ->with(['controlPoint:id,name', 'floorplan:id,customer_id']);
            
        if ($deviceId) {
            $query->where('id', $deviceId);
        }
        
        return $query->get()->unique('id');
    }

    /**
     * Get customer device types with optimized query
     */
    public function getCustomerDeviceTypes(int $customerId)
    {
        return DB::table('device')
            ->join('floorplans', 'device.floorplan_id', '=', 'floorplans.id')
            ->join('control_point', 'device.type_control_point_id', '=', 'control_point.id')
            ->where('floorplans.customer_id', $customerId)
            ->select('device.type_control_point_id', 'control_point.name')
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'type_control_point_id' => $item->type_control_point_id,
                    'name' => $item->name,
                    'type' => $item->type_control_point_id
                ];
            });
    }

    /**
     * Parse date range string into Carbon objects
     */
    public function parseDateRange(string $dateRange): array
    {
        try {
            return array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $dateRange));
        } catch (\Exception $e) {
            Log::error("Error parsing date range: {$dateRange}", ['error' => $e->getMessage()]);
            // Return current month as fallback
            return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    /**
     * Generate week range for table headers
     */
    public function generateWeekRange(Carbon $startDate, Carbon $endDate): array
    {
        $weeks = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $weeks[] = $currentDate->copy();
            $currentDate->addWeek();
        }
        
        return $weeks;
    }

    /**
     * Build consumption table with optimized queries
     */
    public function buildConsumptionTable(int $customerId, Carbon $startDate, Carbon $endDate, ?int $deviceTypeId = null): array
    {
        // Get orders in date range
        $orders = Order::where('customer_id', $customerId)
            ->where('status_id', self::STATUS_APPROVED)
            ->whereBetween('programmed_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        
        // Get devices for this customer
        $devicesQuery = Device::whereHas('floorplan', function($q) use ($customerId) {
            $q->where('customer_id', $customerId);
        })->with(['controlPoint:id,name,code']);
        
        // Filter by device type if specified
        if ($deviceTypeId) {
            $devicesQuery->where('type_control_point_id', $deviceTypeId);
        }
        
        $devices = $devicesQuery->get();
        
        if ($devices->isEmpty() || $orders->isEmpty()) {
            return [];
        }

        // Get incidents for consumption analysis
        $incidents = OrderIncidents::where('question_id', self::DEVICE_CONSUMPTION_QUESTION_ID)
            ->whereIn('order_id', $orders->pluck('id'))
            ->whereIn('device_id', $devices->pluck('id'))
            ->whereNotNull('answer')
            ->get()
            ->groupBy(['device_id', 'order_id']);

        $weeks = $this->generateWeekRange($startDate, $endDate);
        
        $table = [];
        foreach ($devices as $device) {
            $row = [
                'device' => $device->controlPoint->name ?? 'Dispositivo sin punto de control',
                'device_code' => $device->code ?? ($device->controlPoint->code ?? 'N/A') . '-' . $device->id,
                'device_id' => $device->id,
                'weeks' => [],
                'total' => 0
            ];
            
            foreach ($weeks as $weekStart) {
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                // Find orders in this week that have incidents for this device
                $weekConsumption = 0;
                foreach ($orders as $order) {
                    $orderDate = Carbon::parse($order->programmed_date);
                    if ($orderDate->between($weekStart, $weekEnd)) {
                        $deviceIncidents = $incidents->get($device->id, collect());
                        $orderIncident = $deviceIncidents->get($order->id);
                        
                        if ($orderIncident) {
                            $incident = $orderIncident->first();
                            if ($incident) {
                                $answer = $incident->answer ?? 'Nulo';
                                $weekConsumption += self::CONSUMPTION_LEVELS[$answer] ?? 0;
                            }
                        }
                    }
                }
                
                $row['weeks'][] = $weekConsumption;
            }
            
            $row['total'] = array_sum($row['weeks']);
            
            // Only include devices that have some consumption data
            if ($row['total'] > 0) {
                $table[] = $row;
            }
        }
        
        return $table;
    }

    /**
     * Get filtered orders with optimized query
     */
    public function getFilteredOrders(int $customerId, ?int $serviceId = null)
    {
        $query = Order::where('customer_id', $customerId)
            ->where('status_id', self::STATUS_APPROVED)
            ->with(['contract.services:id']); 
            
            
        if ($serviceId) {
            $query->whereHas('services', function($q) use ($serviceId) {
                $q->where('service.id', $serviceId);
            });
        }
        
        return $query->get();
    }

    /**
     * Get total orders by status with single query
     */
    public function getTotalOrdersByStatus(int $customerId): array
    {
        $statusCounts = Order::where('customer_id', $customerId)
            ->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        return [
            'pending' => $statusCounts[self::STATUS_PENDING] ?? 0,
            'accepted' => $statusCounts[self::STATUS_ACCEPTED] ?? 0,
            'finished' => $statusCounts[self::STATUS_FINISHED] ?? 0,
            'verified' => $statusCounts[self::STATUS_VERIFIED] ?? 0,
            'approved' => $statusCounts[self::STATUS_APPROVED] ?? 0,
            'canceled' => $statusCounts[self::STATUS_CANCELED] ?? 0,
        ];
    }

    /**
     * Get devices from orders collection
     */
    public function getDevicesFromOrders($orders)
    {
        if (!$orders instanceof \Illuminate\Support\Collection) {
            return collect();
        }

        // Get all device IDs that have incidents in these orders
        $deviceIds = OrderIncidents::whereIn('order_id', $orders->pluck('id'))
            ->distinct('device_id')
            ->pluck('device_id');

        return Device::whereIn('id', $deviceIds)
            ->with(['controlPoint:id,name,code'])
            ->get();
    }

    /**
     * Get device consumption data with error handling
     */
    public function getDeviceConsumptionData(int $customerId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?int $serviceId = null): array
    {   
        //dd($customerId);
        try {
            $startDate = $startDate ?? Carbon::now()->startOfMonth();
            $endDate = $endDate ?? Carbon::now()->endOfMonth();

            Log::info("Getting device consumption data", [
                'customerId' => $customerId,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'serviceId' => $serviceId
            ]);

            // Get orders for the customer in the date range
            $ordersQuery = Order::where('customer_id', $customerId)
                ->where('status_id', self::STATUS_APPROVED)
                ->whereBetween('programmed_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            if ($serviceId) {
                $ordersQuery->whereHas('services', function($q) use ($serviceId) {
                    $q->where('service.id', $serviceId);
                });
            }
            
            $orders = $ordersQuery->get();
            
            // Get all devices for this customer through floorplans
            $devices = Device::whereHas('floorplan', function($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })->with(['controlPoint:id,name,code'])->get();
            
            // Base response structure
            $response = [
                'consumption' => [
                    'month' => $startDate->month,
                    'devices' => [],
                    'has_data' => false
                ]
            ];

            if ($devices->isEmpty() || $orders->isEmpty()) {
                Log::info("No devices or orders found", [
                    'devices_count' => $devices->count(),
                    'orders_count' => $orders->count()
                ]);
                return $response;
            }

            // Get incidents with consumption data (question_id = 13 for device consumption)
            $consumptionIncidents = OrderIncidents::where('question_id', self::DEVICE_CONSUMPTION_QUESTION_ID)
                ->whereIn('order_id', $orders->pluck('id'))
                ->whereIn('device_id', $devices->pluck('id'))
                ->whereNotNull('answer')
                ->get();

            if ($consumptionIncidents->isEmpty()) {
                Log::info("No consumption incidents found", [
                    'question_id' => self::DEVICE_CONSUMPTION_QUESTION_ID,
                    'order_ids' => $orders->pluck('id')->toArray(),
                    'device_ids' => $devices->pluck('id')->toArray()
                ]);
                return $response;
            }
                
            $orderIncidents = $consumptionIncidents->groupBy('device_id');

            // Process devices data
            $devicesData = [];
            foreach ($devices as $device) {
                $deviceIncidents = $orderIncidents->get($device->id, collect());
                
                if ($deviceIncidents->isEmpty()) {
                    continue;
                }
                
                // Calculate consumption values by normalizing answers
                $consumptions = [];
                foreach ($deviceIncidents as $incident) {
                    $answer = $incident->answer ?? 'Nulo';
                    $normalized = $this->normalizeConsumptionAnswer($answer);
                    $consumptions[] = $normalized;
                }
                
                $devicesData[$device->id] = [
                    'id' => $device->id,
                    'code' => $device->code ?? ($device->controlPoint->code ?? 'N/A') . '-' . $device->id,
                    'type' => $device->type_control_point_id,
                    'name' => $device->controlPoint->name ?? 'Dispositivo sin nombre',
                    'consumptions' => $consumptions,
                    'total_consumption' => array_sum($consumptions)
                ];
            }

            Log::info("Processed devices data", [
                'devices_with_data' => count($devicesData),
                'total_devices' => $devices->count()
            ]);

            $response['consumption']['devices'] = $devicesData;
            $response['consumption']['has_data'] = !empty($devicesData);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error("Error in getDeviceConsumptionData: " . $e->getMessage(), [
                'customerId' => $customerId,
                'startDate' => $startDate?->format('Y-m-d'),
                'endDate' => $endDate?->format('Y-m-d'),
                'serviceId' => $serviceId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'consumption' => [
                    'error' => true,
                    'message' => 'Error al procesar los datos de consumo: ' . $e->getMessage()
                ]
            ];
        }
    }


    /**
     * Normalize consumption answer to numeric value
     */
    private function normalizeConsumptionAnswer(string $answer): float
    {
        // Try exact match first
        if (isset(self::CONSUMPTION_LEVELS[$answer])) {
            return self::CONSUMPTION_LEVELS[$answer];
        }
        
        // Try case-insensitive match
        $lowerAnswer = strtolower(trim($answer));
        $mappings = [
            'nulo' => 0,
            'bajo' => 0.25,
            'medio' => 0.5,
            'alto' => 0.75,
            'total' => 1,
            'completo' => 1,
            'ninguno' => 0,
            'poco' => 0.25,
            'mucho' => 0.75,
            'todo' => 1,
            'null' => 0,
            'low' => 0.25,
            'medium' => 0.5,
            'high' => 0.75,
            'very_high' => 1
        ];
        
        if (isset($mappings[$lowerAnswer])) {
            return $mappings[$lowerAnswer];
        }
        
        // If it's already numeric, use it directly (normalize between 0-1)
        if (is_numeric($answer)) {
            return min(1, max(0, (float)$answer));
        }
        
        // Default to 0 if can't normalize
        Log::warning("Could not normalize consumption answer", ['answer' => $answer]);
        return 0;
    }
} 