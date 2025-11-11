<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ServicePrefix;

class ServicePrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prefixes = [
            [
                'name' => 'Control'
            ],
            [
                'name' => 'Aplicación química'
            ],
            [
                'name' => 'Aplicación de gel'
            ],
            [
                'name' => 'Otro'
            ],
        ];

        ServicePrefix::insert($prefixes);
    }
}
