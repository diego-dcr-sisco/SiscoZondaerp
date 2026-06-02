<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientReportService
{
    public function getReportData(
        string $startDate, 
        string $endDate, 
        string $type = 'all', 
        array $metrics = []
        )
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();
        $startDateOnly = $start->format('Y-m-d');
        $endDateOnly = $end->format('Y-m-d');

        $query = DB::table('customer as c')
            ->select(
                'c.id',
                'c.code',
                'c.name',
                DB::raw('COALESCE(c.tax_name, c.name, "N/A") as businessname'),
                DB::raw('COALESCE(c.tel, c.phone, "N/A") as tel'),
                'c.email',
                'c.created_at',

                // Primera orden GLOBAL
                DB::raw('(SELECT MIN(programmed_date) 
                FROM `order` 
                WHERE customer_id = c.id) as first_order_global'),

                // Primera y última orden EN EL RANGO
                DB::raw('(SELECT MIN(programmed_date) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND programmed_date 
                BETWEEN "' . $startDateOnly . '" 
                AND "' . $endDateOnly . '") as first_order'),
                
                DB::raw('(SELECT MAX(programmed_date) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND programmed_date 
                BETWEEN "' . $startDateOnly . '" 
                AND "' . $endDateOnly . '") as last_order'),

            // Órdenes en el rango
                DB::raw('(SELECT COUNT(id) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND programmed_date 
                BETWEEN "' . $startDateOnly . '" 
                AND "' . $endDateOnly . '") as total_orders_in_range'),

                DB::raw('(
                SELECT COUNT(DISTINCT dp.device_id)
                FROM device_pest dp
                JOIN `order` o ON o.id = dp.order_id
                WHERE o.customer_id = c.id
                ) as devices_count'),

                DB::raw('(
                SELECT GROUP_CONCAT(DISTINCT cp.name SEPARATOR ", ")
                FROM devices dv
                JOIN device d ON d.id = dv.device_id
                JOIN control_point cp ON cp.id = d.type_control_point_id
                WHERE dv.order_id IN (
                SELECT id FROM `order` WHERE customer_id = c.id
                )) as device_types'),

                DB::raw('(
                SELECT COUNT(*)
                FROM device_pest dp
                JOIN `order` o ON o.id = dp.order_id
                WHERE o.customer_id = c.id
                ) as pest_count'),

            // TIPOS DE PLAGA
                DB::raw('(
                SELECT GROUP_CONCAT(DISTINCT pcg.category SEPARATOR ", ")
                FROM device_pest dp
                JOIN pest_catalog pc ON pc.id = dp.pest_id
                JOIN pest_category pcg ON pcg.id = pc.pest_category_id
                JOIN `order` o ON o.id = dp.order_id
                WHERE o.customer_id = c.id
                ) as pest_types')
            )

        // Solo clientes con órdenes en el rango
            ->whereRaw('(SELECT COUNT(id) 
            FROM `order` 
            WHERE customer_id = c.id 
            AND programmed_date 
            BETWEEN "' . $startDateOnly . '" 
            AND "' . $endDateOnly . '") > 0');

            $customers = $query->get();

        // Tipo: nuevo vs recurrente
            $customers = $customers->map(function ($c) use ($startDateOnly) {
            $c->calculated_type = ($c->first_order_global &&
            \Carbon\Carbon::parse($c->first_order_global)->gte(\Carbon\Carbon::parse($startDateOnly)))
            ? 'new'
            : 'recurring';
            return $c;
            });

    // Filtro por tipo
        if ($type === 'new') {
            $customers = $customers->filter(fn($c) => $c->calculated_type === 'new');
        } elseif ($type === 'recurring') {
            $customers = $customers->filter(fn($c) => $c->calculated_type === 'recurring');
        }

        return $customers->values();
    }
}