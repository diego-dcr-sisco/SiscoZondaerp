<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Day;

class DaysWeekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days_week = [
            ['name' => 'Lunes'],
            ['name' => 'Martes'],
            ['name' => 'Miércoles'],
            ['name' => 'Jueves'],
            ['name' => 'Viernes'],
            ['name' => 'Sábado'],
            ['name' => 'Domingo']
        ];

        Day::insert($days_week);
    }
}
