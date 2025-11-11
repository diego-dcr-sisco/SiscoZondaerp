<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'category_id' => 1,
                'name' => 'SISTEMAS ECOLOGICOS PARA EL CONTROL DE PLAGAS SA DE CV | LUIS MARIO MARTINEZ',
                'rfc' => 'SEC9108274P0',
                'address' => 'VILLA PRIMAVERA No 640-3 VILLAS DE LINDA VISTA. C.P.  64540, MONTERREY NL',
                'phone' => '5522000210',
                'email' => 'luis.martinez@veseris.com',
            ],
            [
                'category_id' => 2,
                'name' => 'SUMINISTROS ECOLOGICOS DE CELAYA | SALVADOR MARTINEZ',
                'rfc' => 'SEC161220DG5',
                'address' => 'CIRCUITO DEL PEDREGAL ORIENTE 209, COL. QUINTA SANTA. MARIA, CELAYA CELAYA, CP: 38010',
                'phone' => '4615468734',
                'email' => 'facturas@ecosuministros.com.mx'
            ],
            [
                'category_id' => 3,
                'name' => 'AVENDRAN HOLDING | ARLETE ZAPATA',
                'rfc' => 'AHO181101R5A',
                'address' => 'VERACRUZ',
                'phone' => '222 705 0778',
                'email' => 'veracruz@elsembrador.com.mx',
            ],
            [
                'category_id' => 3,
                'name' => 'AGROSERVICIOS NACIONALES | DAVID CORTES',
                'rfc' => 'ANA771130Q81',
                'address' => 'TECOMAN',
                'phone' => '313325 2716',
                'email' => 'David Cortes Ramos tecoman.mostrador@ansa.cc',
            ],
            [
                'category_id' => 4,
                'name' => 'H&M IMPORTACIONES | RUTH NAVARRO',
                'rfc' => 'HAM120214GP4',
                'address' => 'QUERETARO',
                'phone' => '442 2448752',
                'email' => ' ',
            ],
            [
                'category_id' => 5,
                'name' => 'INSUMOS AGRICOLAS, SA DE CV | SRITA. JULIETA',
                'rfc' => 'IAS040803UA7',
                'address' => 'SAN LUIS POTOSI',
                'phone' => '444 8216818',
                'email' => 'insumosagricolas.venta@gmail.com',
            ],
            [
                'category_id' => 6,
                'name' => 'PROVEEDORA DE SEGURIDAD INDUSTRIAL DEL GOLFO | MARIBEL MERCADO',
                'rfc' => 'PSI8906083F8',
                'address' => 'SUCURSAL INDUSTRIAS SLP',
                'phone' => 'gk',
                'email' => 'mostradorslp@vallenproveedora.com.mx',
            ],
            [
                'category_id' => 7,
                'name' => 'DIKEN INTERNATIONAL, S DE RL DE CV | MIGUEL SALDAÑA',
                'rfc' => 'DIN150103FC9',
                'address' => 'SAN LUIS POTOSI',
                'phone' => '444 216 2063',
                'email' => 'miguel.saldana@dikeninternational.com',
            ],
            [
                'category_id' => 8,
                'name' => 'FABRICA DE CEPILLOS INDUSTRIALES EL ANGEL, SA DE CV | JAZMINE LEMUS BUSTOS',
                'rfc' => '3RW111108FCI',
                'address' => 'CALLE UNO # 110, CAMPESTRE GUADALUPANA, NEZAHUALCOYOTL C.P. 57120',
                'phone' => '55 61923816',
                'email' => 'cepilloselangel@prodigy.net.mx',
            ],
            [
                'category_id' => 9,
                'name' => 'UNITAM (UNIFORMES DE TAMPICO, SA DE CV) | SRITA. DANIELA',
                'rfc' => 'UTA820628TV3',
                'address' => 'SUCURSAL EL PASEO',
                'phone' => '444 8440905',
                'email' => ' ',
            ],
            [
                'category_id' => 10,
                'name' => 'DALCE DEL CENTRO, SA DE CV | DELTA RENE ALCOZER',
                'rfc' => 'DCE960513J37',
                'address' => 'SAN LUIS POTOSI',
                'phone' => '4441692180',
                'email' => 'alcocer.delta@dalce.com.mx',
            ],
            [
                'category_id' => 11,
                'name' => 'GRUPO HICA (REPRESENTACIONES Y SERVICIOS HI-CA) | DEPTO. REFACCIONES',
                'rfc' => 'RSH960126R38',
                'address' => 'SAN LUIS POTOSI',
                'phone' => '444 8148084',
                'email' => ' ',
            ],
        ];

        Supplier::insert($suppliers);
    }
}
