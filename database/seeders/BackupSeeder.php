<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\OrderService;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ContractService;
use App\Models\PropagateService;

class BackupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener datos principales
        $order_services = OrderService::whereIn('service_id', [44, 45, 98])->get();
        
        $orders = Order::where('contract_id', 60)
                     ->whereIn('id', $order_services->pluck('order_id'))
                     ->get()
                     ->map(function ($order) {
                         $order->folio = null; // Forzar folio a NULL
                         return $order;
                     });
        
        $order_products = OrderProduct::whereIn('order_id', $orders->pluck('id'))->get();
        
        // 2. Obtener datos adicionales solicitados
        $contract_services = ContractService::where('contract_id', 60)->get();
        $propagate_services = PropagateService::where('contract_id', 60)
                                           ->whereIn('order_id', $orders->pluck('id'))
                                           ->get();
        
        // 3. Generar SQL para cada tabla
        $sqlContent = "-- SQL INSERT IGNORE statements generated at " . now()->toDateTimeString() . "\n\n";
        
        //$sqlContent .= $this->generateSection('ORDER SERVICES', $order_services, 'order_services');
        //$sqlContent .= $this->generateSection('ORDERS (with NULL folio)', $orders, 'orders');
        //$sqlContent .= $this->generateSection('ORDER PRODUCTS', $order_products, 'order_products');
        $sqlContent .= $this->generateSection('CONTRACT SERVICES', $contract_services, 'contract_services');
        $sqlContent .= $this->generateSection('PROPAGATE SERVICES', $propagate_services, 'propagate_services');
        
        // 4. Guardar en archivo
        $filename = 'full_contract_data_' . now()->format('Ymd_His') . '.sql';
        Storage::disk('local')->put('seeder_sql/' . $filename, $sqlContent);
        
        // 5. Mostrar resumen
        $this->command->info('Archivo SQL generado: storage/app/seeder_sql/' . $filename);
        $this->command->info('Resumen:');
        $this->command->info('- Order Services: ' . $order_services->count());
        $this->command->info('- Orders: ' . $orders->count());
        $this->command->info('- Order Products: ' . $order_products->count());
        $this->command->info('- Contract Services: ' . $contract_services->count());
        $this->command->info('- Propagate Services: ' . $propagate_services->count());
    }
    
    protected function generateSection($title, $collection, $tableName): string
    {
        return "-- " . $title . "\n" . 
               $this->generateInsertIgnoreStatements($collection, $tableName) . 
               "\n\n";
    }
    
    protected function generateInsertIgnoreStatements($collection, $tableName): string
    {
        if ($collection->isEmpty()) {
            return "-- No hay registros para la tabla {$tableName}";
        }
        
        $columns = array_keys($collection->first()->getAttributes());
        $sql = "INSERT IGNORE INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";
        
        $values = [];
        foreach ($collection as $item) {
            $attributes = array_map(function ($value) {
                if (is_null($value)) {
                    return 'NULL';
                }
                // Escapar comillas simples correctamente
                return "'" . addslashes($value) . "'";
            }, $item->getAttributes());
            
            $values[] = "    (" . implode(', ', $attributes) . ")";
        }
        
        $sql .= implode(",\n", $values) . ";";
        
        return $sql;
    }
}