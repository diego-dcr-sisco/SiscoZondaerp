<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class CustomerReportService
{
    public function getReportData(array $filters)
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end   = Carbon::parse($filters['end_date'])->endOfDay();
        $type  = $filters['client_type'] ?? 'all';

        $query = Customer::query()

            // ✔ clientes con actividad en rango (correcto y seguro)
            ->whereHas('service_orders', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })

            // ✔ agregaciones seguras (sin depender de nombres raros de Laravel)
            ->addSelect([
                'first_order' => function ($q) {
                    $q->selectRaw('MIN(created_at)')
                        ->from('service_orders')
                        ->whereColumn('service_orders.customer_id', 'customers.id');
                },
                'last_order' => function ($q) {
                    $q->selectRaw('MAX(created_at)')
                        ->from('service_orders')
                        ->whereColumn('service_orders.customer_id', 'customers.id');
                }
            ]);

        // ✔ filtros seguros (SIN HAVING)
        if ($type === 'new') {
            $query->whereBetween(
                \DB::raw('(SELECT MIN(created_at) FROM service_orders WHERE service_orders.customer_id = customers.id)'),
                [$start, $end]
            );
        }

        if ($type === 'recurring') {
            $query->whereRaw(
                '(SELECT MIN(created_at) FROM service_orders WHERE service_orders.customer_id = customers.id) < ?',
                [$start]
            );
        }

        // métricas
        $this->applyMetrics($query, $filters, $start, $end);

        $customers = $query->get();

        // clasificación segura
        return $customers->map(function ($customer) use ($start) {

            if (!$customer->first_order) {
                $customer->computed_type = 'Sin datos';
                return $customer;
            }

            $customer->computed_type =
                Carbon::parse($customer->first_order)->lt($start)
                    ? 'Recurrente'
                    : 'Nuevo';

            return $customer;
        });
    }

    private function applyMetrics($query, array $filters, $start, $end)
    {
        if (!empty($filters['inc_orders_count'])) {
            $query->withCount([
                'service_orders as orders_count' => function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                }
            ]);
        }

        if (!empty($filters['inc_devices_count'])) {
            $query->withCount('devices as devices_count');
        }

        if (!empty($filters['inc_device_types'])) {
            $query->with(['devices:id,customer_id,type']);
        }

        if (!empty($filters['inc_pests_count']) || !empty($filters['inc_pest_types'])) {
            $query->withCount([
                'pestDetections as pests_count' => function ($q) use ($start, $end) {
                    $q->whereBetween('detected_at', [$start, $end]);
                }
            ]);
        }
    }
}