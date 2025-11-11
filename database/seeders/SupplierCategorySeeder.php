<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'PRODUCTOS/ INSUMOS'],
            ['id' => 2, 'name' => 'EQUIPOS JACTO, MALLA ANTIPAJAROS'],
            ['id' => 3, 'name' => 'PRODUCTOS PLAGAS'],
            ['id' => 4, 'name' => 'ATRAYENTE 250 GMS FLYBUSTHER'],
            ['id' => 5, 'name' => 'BOMBAS MANUALES, REFACCIONES, PASTILLAS DE FOSFURO'],
            ['id' => 6, 'name' => 'EPP / SE MANEJA REFERENCIA DE PAGO No 230077   '],
            ['id' => 7, 'name' => 'LK-500, CLEAN CONTINUS'],
            ['id' => 8, 'name' => 'CEPILLOS LAVADO BOTELLAS SLP Y GDL'],
            ['id' => 9, 'name' => 'CAMISAS, BATAS, OVEROLES NORMALES E IGNIFUGOS'],
            ['id' => 10, 'name' => 'PAPEL DE BAÑO, SANITAS, JABON, FIBRAS PARA LAVADO ENVASE TERKLEEN'],
            ['id' => 11, 'name' => 'ASPERSORAS, BOMBAS MANUALES, ACEITE DOS TIEMPOS Y DESENGRASANTE'],
            ['id' => 12, 'name' => 'Productos Químicos'],
            ['id' => 13, 'name' => 'Materiales de Construcción'],
            ['id' => 14, 'name' => 'Equipos de Protección Personal'],
            ['id' => 15, 'name' => 'Herramientas'],
            ['id' => 16, 'name' => 'Maquinaria'],
            ['id' => 17, 'name' => 'Suministros de Oficina'],
            ['id' => 18, 'name' => 'Tecnología'],
            ['id' => 19, 'name' => 'Servicios de Limpieza'],
            ['id' => 20, 'name' => 'Transporte y Logística'],
            ['id' => 21, 'name' => 'Mantenimiento'],
            ['id' => 22, 'name' => 'Consultoría'],
            ['id' => 23, 'name' => 'Seguridad'],
            ['id' => 24, 'name' => 'Alimentos y Bebidas'],
            ['id' => 25, 'name' => 'Muebles y Decoración'],
            ['id' => 26, 'name' => 'Publicidad y Marketing'],
            ['id' => 27, 'name' => 'Servicios Financieros'],
            ['id' => 28, 'name' => 'Recursos Humanos'],
            ['id' => 29, 'name' => 'Telecomunicaciones'],
            ['id' => 30, 'name' => 'Energía y Combustibles'],
        ];

        DB::table('supplier_categories')->upsert($categories, ['id'], ['name']);

    }
}
