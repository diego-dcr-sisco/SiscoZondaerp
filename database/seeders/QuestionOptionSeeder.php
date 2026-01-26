<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionOption;

class QuestionOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $values = [
            /*1*/ 'Sí o No',
            /*2*/ 'Bien, Regular o Mal',
            /*3*/ 'Numero Entero',
            /*4*/ 'Numero Decimal',
            /*5*/ 'Texto Corto',
            /*6*/ 'Texto Largo',
            /*7*/ 'Nulo, Bajo o Poco',
            /*8*/ 'Porcentaje',
            /*9*/ 'Evaluacion',
            //'Muy mal, Mal, Regular, Bien, Perfecto',
            /*10*/ 'Perfecto, Bien, Regular, Mal o Muy mal',
            /*11*/ 'Consumo Total, Alto, Medio, Bajo o Nulo',
            //'Nulo,Bajo,Medio,Alto',
            /*12*/ 'Tipo de plaga',
            /*13*/ 'Correcta, Desaparecida, Inaccesible, Reducida, Rota, Apagada',
            /*14*/ 'Correcto, Estropeado, Completamente comido, Parcialmente, Comido, Sin cebo',
            /*15*/ 'Huella, Deposicion, Restos comida, Pelos, Rata, Raton, Cebo roido o No hay deteccion',
            /*16*/ 'Area interior, Area exterior o Perimetro'
        ];
        $descriptions = [
            'El aplicador podra contestar entre un',
            'El aplicador podra contestar entre valores',
            'El aplicador podra contestar con un',
            'El aplicador podra contestar con un',
            'El aplicador podra contestar con un',
            'El aplicador podra contestar con un',
            'El aplicador podra contestar entre valores',
            'El aplicador podra contestar con un',
            'El aplicador podra contestar con un valor',
            'El aplicador podra contestar con un valor a elegir entre',
            'El aplicador podra contestar con un valor a elegir entre',
            'El aplicador podra contestar con una plaga a elegir de entre las que gestiona el punto de control',
            'El aplicador podra contestar entre valores',
            'El aplicador podra contestar entre valores',
            'El aplicador podra contestar entre valores',
            'El aplicador podra contestar con'
        ];
        for($i = 0; $i<count($values); $i++)
        {
            QuestionOption::create([
                'value' =>  $values[$i],
                'description' => $descriptions[$i]
            ]); 
        }
    }
}
