<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lot;
use App\Models\MovementProduct;

class AdjustLots extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*protected $fillable = [
            'id',
            'warehouse_movement_id',
            'movement_id',
            'warehouse_id',
            'product_id',
            'lot_id',
            'amount',
            'created_at',
            'updated_at'
        ];
        
        protected $fillable = [
        'id',
        'product_id',
        'warehouse_id',
        'registration_number',
        'expiration_date',
        'amount',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];
        */

        $lots = Lot::all();

        foreach ($lots as $lot) {
            $lot->update([
                'amount' => 100,
            ]);
            $lot->save();

            MovementProduct::updateOrCreate(
                [
                    'lot_id' => $lot->id,
                    'product_id' => $lot->product_id,
                    'warehouse_id' => $lot->warehouse_id,
                ],
                [
                    'warehouse_movement_id' => 1,
                    'movement_id' => 1, 
                    'amount' => $lot->amount,
                ]
            );
        }
    }
}
