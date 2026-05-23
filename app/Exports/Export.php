<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Export implements  FromCollection, WithHeadings
{
    protected $data;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data){
        $this->data = $data;
    }
    public function collection(){
        return collect($this->data);
    }
    public function headings(): array
    {
        return [
            'Nombre del Cliente',
            'Teléfono del Cliente',
            'Dirección del Cliente',
            'Nombre del Servicio',
            'Costo del Servicio',
            'Hora de Creación de la Orden',
            'Nombre del Técnico',
        ];
    }
}
