<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ContractType;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contracts = [
            [ 'name' => 'Prueba', ],
            [ 'name' => 'Contrato parcial', ],
            [ 'name' => 'Contrato total', ],
        ];

        foreach ($contracts as $contract) {
            ContractType::create($contract);
        }
    }
}
