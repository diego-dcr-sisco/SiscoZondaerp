<?php

namespace Database\Seeders;

use App\Models\Filenames;
use App\Models\User;

use App\Models\UserFile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = Filenames::where('type', 'user')->get();
        $users = User::where('type_id', 1)->get();

        foreach($users as $user){
            foreach($files as $file) {
                UserFile::insert([
                    'user_id' => $user->id,
                    'filename_id' => $file->id,
                ]);
            }
        }
    }
}
