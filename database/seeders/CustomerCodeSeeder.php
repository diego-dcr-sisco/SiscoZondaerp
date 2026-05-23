<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\Customer;

class CustomerCodeSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();

        foreach ($customers as $customer) {
            if (!$customer->code) {
                $name = strtoupper(preg_replace('/[^A-Za-z]/', '', $customer->name));

                // 2. Obtener las primeras 2-3 letras del nombre
                $prefix = substr($name, 0, rand(2, 3));

                // 3. Generar 2-3 caracteres alfanuméricos aleatorios
                $randomPart = Str::upper(Str::random(rand(2, 3)));

                // 4. Combinar el prefijo con la parte aleatoria
                $code = $prefix . $randomPart;

                // 5. Asegurar unicidad en la base de datos
                $originalCode = $code;
                $counter = 1;

                while (Customer::where('code', $code)->exists()) {
                    $code = $originalCode . $counter; // Si existe, agrega un número al final
                    $counter++;
                }

                $customer->code = $code;
                $customer->save();
            }
        }
    }
}
