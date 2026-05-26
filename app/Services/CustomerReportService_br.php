<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Device;
use App\Models\OrderPest;
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

        // PASO 3: Fechas históricas masivas
        $historicalData = DB::table($ordersTable)
            ->whereIn('customer_id', $activeCustomerIds)
            ->select([
                'customer_id',
                DB::raw('MIN(created_at) as first_order'),
                DB::raw('MAX(created_at) as last_order'),
                DB::raw("SUM(CASE WHEN created_at BETWEEN '{$start}' AND '{$end}' THEN 1 ELSE 0 END) as total_orders_in_range")
            ])
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

/// PASO 4A: Forzar colección vacía para evitar errores de estructura en dispositivos
        $devicesData = collect();

        // PASO 4B: Cargar Plagas (Cantidad de asociadas y Tipos detectados)
        $pestsData = collect();
        if (in_array('inc_pests_count', $metrics) || in_array('inc_pest_types', $metrics)) {
            $orderPestTable = (new OrderPest())->getTable();
            
            $pestsData = DB::table($orderPestTable . ' as op')
                ->join($ordersTable . ' as o', 'o.id', '=', 'op.order_id')
                ->join('pest_catalog as pc', 'pc.id', '=', 'op.pest_id') // <-- CAMBIADO 'op.pest_catalog_id' por 'op.pest_id'
                ->whereIn('o.customer_id', $activeCustomerIds)
                ->whereBetween('o.created_at', [$start, $end])
                ->select([
                    'o.customer_id',
                    DB::raw('COUNT(op.id) as total_pests_count'),
                    DB::raw('GROUP_CONCAT(DISTINCT pc.name SEPARATOR ", ") as pest_types_names')
                ])
                ->groupBy('o.customer_id')
                ->get()
                ->keyBy('customer_id');
        }

        // PASO 4C: Unificar de manera ultra eficiente en memoria RAM
        $processed = $customers->map(function ($customer) use ($historicalData, $devicesData, $pestsData, $start) {
            $history = $historicalData->get($customer->id);
            $devices = $devicesData->get($customer->id);
            $pests   = $pestsData->get($customer->id);

            // Inyección de Órdenes
            $customer->first_order = $history ? $history->first_order : null;
            $customer->last_order  = $history ? $history->last_order : null;
            $customer->total_orders_in_range = $history ? $history->total_orders_in_range : 0;

            // Inyección de Dispositivos
            $customer->devices_count = $devices ? $devices->total : 0;
            $customer->device_types  = $devices ? $devices->types : 'N/A';

            // Inyección de Plagas con su cantidad total y tipos detectados
            $customer->pests_count = $pests ? $pests->total_pests_count : 0;
            $customer->pest_types  = $pests ? $pests->pest_types_names : 'Ninguna';

            // Clasificación
            if ($customer->first_order && Carbon::parse($customer->first_order)->lt($start)) {
                $customer->calculated_type = 'recurrent';
            } else {
                $customer->calculated_type = 'new';
            }

            return $customer;
        });

        // PASO 5: Filtrar por tipo de cliente seleccionado
        if ($clientType !== 'all') {
            $processed = $processed->where('calculated_type', $clientType);
        }

        return $processed->values();
    }
}   