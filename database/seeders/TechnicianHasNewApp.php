<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TechnicianHasNewApp extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user_location_ids = UserLocation::select('user_id')->get()->unique();
        $users = User::whereIn('id', $user_location_ids)->get();

        foreach ($users as $i => $user) {
            echo "| Nombre: $user->name |" . PHP_EOL;
        }
    }
}
