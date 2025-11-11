<?php

namespace App\Http\Controllers;

use App\Models\CustomerZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    protected $zones = [
        'San Luis',
        'Aguascalientes',
        'Rioverde',
        'Cd. Valles',
        'Tamazunchale',
        'Jalisco',
        'Tecoman',
        'Culiacan',
        'Durango',
        'Guanajuato',
        'Monterrey',
        'Queretaro',
        'Veracruz',
        'Yucatan',
        'Matamoros'
    ];
    
    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Zonas' => '/customer-zones',
        'Consumos' => '/consumptions/',
        // 'Pedidos' => '/consumptions',
        // 'Productos en ordenes' => '/stock/orders-products',
        // 'Estadisticas' => 'stock/analytics',
        // 'Compras' => '/purchase-requisition/purchases',
    ];

    
    /**
     * Get unique zone names for dropdown
     */
    public function __invoke(Request $request)
    {
        // Get distinct zone names
        $zones = CustomerZone::select('zone')
            ->distinct()
            ->orderBy('zone')
            ->pluck('zone')
            ->toArray();
        
        // If there are no zones in the database, provide some default options
        if (empty($zones)) {
            $zones = [
                'San Luis',
                'Aguascalientes',
                'Rioverde',
                'Cd. Valles',
                'Tamazunchale',
                'Jalisco',
                'Tecoman',
                'Culiacan',
                'Durango',
                'Guanajuato',
                'Monterrey',
                'Queretaro',
                'Veracruz',
                'Yucatan',
                'Matamoros'
            ];
        }
        
        return response()->json([
            'success' => true,
            'zones' => $zones
        ]);
    }
}
