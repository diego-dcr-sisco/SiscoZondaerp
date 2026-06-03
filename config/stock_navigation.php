<?php

return [
    'items' => [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Consumos' => [
            'Nuevos' => '/consumptions',
            'Por cliente' => '/stock/consumptions/by-customer',
            'En ordenes' => '/stock/movements/orders',
        ],
        'Estadisticas' => '/stock/analytics',
        'Zonas comerciales' => '/comercial-zones',
        'Compras' => '/purchase-requisition/purchases',
    ],
];
