<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company1 = Company::create([
            'name' => 'Siscoplagas',
        ]);

        $company2 = Company::create([
            'name' => 'Terkleen',
        ]);
    }
}
