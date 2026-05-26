<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerReportService_br
{
    public function getReportData($startDate, $endDate, $clientType, array $metrics)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        $customerTable = 'customer';
        $ordersTable   = 'order';
        
        // PASO 1: Obtener IDs de clientes activos en el rango de fechas
        $activeCustomerIds = DB::table($ordersTable)
            ->whereBetween('created_at', [$start, $end])
            ->distinct()
            ->pluck('customer_id')
            ->toArray();

        if (empty($activeCustomerIds)) {
            return collect();
        }

        // PASO 2: Traer datos base de los Clientes
        $customers = DB::table($customerTable)
            ->whereIn('id', $activeCustomerIds)
            ->select(['id', 'code', 'name', 'businessname', 'tax_name', 'tel', 'phone', 'email', 'created_at'])
            ->get();

        // PASO 3: Obtener métricas históricas de órdenes
        $ordersData = DB::table($ordersTable)
            ->whereIn('customer_id', $activeCustomerIds)
            ->select([
                'customer_id',
                DB::raw("SUM(CASE WHEN created_at BETWEEN '{$start}' AND '{$end}' THEN 1 ELSE 0 END) as total_orders_in_range")
            ])
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        // ==========================================
        // PASO 4A: Cargar Dispositivos Reales (vía floorplans)
        // ==========================================
        $devicesData = collect();
        if (in_array('inc_has_devices', $metrics) || in_array('inc_devices_count', $metrics) || in_array('inc_device_types', $metrics)) {
            $devicesData = DB::table('device as d')
                ->join('floorplans as f', 'f.id', '=', 'd.floorplan_id')
                ->whereIn('f.customer_id', $activeCustomerIds)
                ->select([
                    'f.customer_id',
                    DB::raw('COUNT(d.id) as total_devices'),
                    DB::raw('GROUP_CONCAT(DISTINCT d.code SEPARATOR ", ") as device_types_list')
                ])
                ->groupBy('f.customer_id')
                ->get()
                ->keyBy('customer_id');
        }

        // ==========================================
        // PASO 4B: Cargar Plagas Reales (¡CORREGIDO vía device_pest!)
        // ==========================================
        $pestsData = collect();
        if (in_array('inc_pests_count', $metrics) || in_array('inc_pest_types', $metrics)) {
            // Conectamos la tabla de 90k filas cruzando por el dispositivo hasta llegar al cliente
            $pestsData = DB::table('device_pest as dp')
                ->join('device as d', 'd.id', '=', 'dp.device_id')
                ->join('floorplans as f', 'f.id', '=', 'd.floorplan_id')
                ->join('pest_catalog as pc', 'pc.id', '=', 'dp.pest_id')
                ->whereIn('f.customer_id', $activeCustomerIds)
                ->select([
                    'f.customer_id',
                    DB::raw('COUNT(dp.id) as total_pests'),
                    DB::raw('GROUP_CONCAT(DISTINCT pc.name SEPARATOR ", ") as pest_names_list')
                ])
                ->groupBy('f.customer_id')
                ->get()
                ->keyBy('customer_id');
        }

        // ==========================================
        // PASO 4C: Inyectar datos calculados en cada objeto Cliente
        // ==========================================
        foreach ($customers as $customer) {
            $cid = $customer->id;

            // Mapeo de Órdenes
            $ordersCount = $ordersData[$cid]->total_orders_in_range ?? 0;
            $customer->total_orders_in_range = $ordersCount;
            $customer->calculated_type       = ($ordersCount <= 1) ? 'new' : 'recurrent';

            // Mapeo de Dispositivos
            $devInfo = $devicesData[$cid] ?? null;
            $customer->devices_count = $devInfo->total_devices ?? 0;
            $customer->device_types  = ($devInfo && $devInfo->total_devices > 0) ? $devInfo->device_types_list : 'N/A';

            // Mapeo de Plagas Reales de la tabla device_pest
            $pestInfo = $pestsData[$cid] ?? null;
            $customer->pests_count = $pestInfo->total_pests ?? 0;
            $customer->pest_types  = ($pestInfo && $pestInfo->total_pests > 0) ? $pestInfo->pest_names_list : 'Ninguna';
        }

        // PASO 5: Filtrar la colección final por tipo
        if ($clientType !== 'all') {
            $customers = $customers->where('calculated_type', $clientType);
        }

        return $customers->values();
    }
}