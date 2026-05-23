<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("warehouse")->insert(
            [
                'id' => 1,
                'branch_id' => 1,
                'name' => 'Zonda Matriz',
                'allow_material_receipts' => '1',
                'is_active' => '1',
                'is_matrix' => '1',
            ]
        );

    }
}
