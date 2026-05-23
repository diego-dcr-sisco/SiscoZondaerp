<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\Technician;
use App\Models\Administrative;
use App\Models\User;

use App\Models\WarehouseProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseProductRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $lots = Lot::all();

        foreach ($lots as $lot) {
            WarehouseProduct::insert([
                'warehouse_id' => $lot->warehouse_id,
                'product_id' => $lot->product_id,
                'lot_id' => $lot->id,
                'amount' => $lot->amount
            ]);
        }

        //Technician::whereNotIn('user_id', User::pluck('id'))->delete();
        //Administrative::whereNotIn('user_id', User::pluck('id'))->delete();
    }
}
