<?php

namespace Database\Seeders;

use App\Models\ContractService;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdjustContractServices extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contract_id = 231;
        $execution_frequency_id = 4;
        $interval = 1;

        $new_exec_freq_id = 1;
        $new_interval = 0;
        $days = [];

        $cs = ContractService::where('contract_id', $contract_id)
            ->where('execution_frequency_id', $execution_frequency_id)
            ->where('interval', $interval)
            ->first();

        if($cs) {
            $orders = Order::where('contract_id', $contract_id)
                ->where('setting_id', $cs->id)
                ->get();

            $days = $orders->pluck('programmed_date')->toArray();

            $cs->update([
                'execution_frequency_id' => $new_exec_freq_id,
                'interval' => $new_interval,
                'days' => json_encode($days[0])
            ]);
        }
    }
}
