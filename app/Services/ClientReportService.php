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
        $start = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
        $end   = Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s');

        $query = DB::table('customer as c')
            ->select(
                'c.id',
                'c.code',
                'c.name',
                DB::raw('COALESCE(c.businessname, c.tax_name, "N/A") as businessname'),
                DB::raw('COALESCE(c.tel, c.phone, "N/A") as tel'),
                'c.email',
                'c.created_at',

                // Primera orden GLOBAL
                DB::raw('(SELECT MIN(created_at) 
                FROM `order` 
                WHERE customer_id = c.id) as first_order_global'),

                // Primera y última orden EN EL RANGO
                DB::raw('(SELECT MIN(created_at) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND created_at 
                BETWEEN "' . $start . '" 
                AND "' . $end . '") as first_order'),
                
                DB::raw('(SELECT MAX(created_at) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND created_at 
                BETWEEN "' . $start . '" 
                AND "' . $end . '") as last_order'),

            // Órdenes en el rango
                DB::raw('(SELECT COUNT(id) 
                FROM `order` 
                WHERE customer_id = c.id 
                AND created_at 
                BETWEEN "' . $start . '" 
                AND "' . $end . '") as total_orders_in_range'),

                DB::raw('(
                SELECT COUNT(DISTINCT dp.device_id)
                FROM device_pest dp
                JOIN `order` o ON o.id = dp.order_id
                WHERE o.customer_id = c.id
                ) as devices_count'),

                DB::raw('(
                SELECT GROUP_CONCAT(DISTINCT d.code SEPARATOR ", ")
                FROM devices dv
                JOIN device d ON d.id = dv.device_id
                WHERE dv.customer_id = c.id
                ) as device_types'),

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
            AND created_at 
            BETWEEN "' . $start . '" 
            AND "' . $end . '") > 0');

            $customers = $query->get();

        // Tipo: nuevo vs recurrente
            $customers = $customers->map(function ($c) use ($start) {
            $c->calculated_type = ($c->first_order_global &&
            \Carbon\Carbon::parse($c->first_order_global)->gte(\Carbon\Carbon::parse($start)))
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