<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\OrderService;
use App\Models\Order;
use Google\Service\AirQuality\Resource\Forecast;

class SearchQuerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer_id = 330;
        $contract_id = 250;
        //$services_ids = [144, 58];
        $service_id = 58;

        $customer = \App\Models\Customer::find($customer_id);

        $orders = Order::where('customer_id', $customer_id)
            ->where('contract_id', $contract_id)
            ->get();

        echo "------------------- Orders for Customer ID ({$customer->id}): {$customer->name} and Contract ID: {$contract_id} --------------------- \n";
        /*foreach ($services_ids as $service_id) {
            $fetch_order_ids = OrderService::where('service_id', $service_id)
                ->whereIn('order_id', $orders->pluck('id')->toArray())
                ->pluck('order_id')
                ->toArray();

            $fetch_orders = Order::whereIn('id', $fetch_order_ids)->get();
            echo "------------------- Orders for Service ID: {$service_id} --------------------- \n";
            
            foreach ($fetch_orders as $index => $order) {
                $status_name = isset($order->status->name) ? $order->status->name : '-';
                $pos = $index + 1;
                echo " {$pos}) Order ID: {$order->id}, Service ID: {$service_id}, Customer ID: {$order->customer_id}, Status: {$status_name}\n";
            }
        }*/ 
        
        $fetch_order_ids = OrderService::where('service_id', $service_id)
            ->whereIn('order_id', $orders->pluck('id')->toArray())
            ->pluck('order_id')
            ->toArray();

        // Eliminar ordenes
        $delete_cant = Order::whereIn('id', $fetch_order_ids)->count();
        echo "------------------- Deleting Orders for Service ID: {$service_id} --------------------- \n";
        echo "Total Orders to Delete: {$delete_cant} \n";
        Order::whereIn('id', $fetch_order_ids)->delete();
    }
}
