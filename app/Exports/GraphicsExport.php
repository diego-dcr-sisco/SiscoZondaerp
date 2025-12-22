<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class GraphicsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected $data;
    protected $graphType;
    protected $headers;
    protected $title;

    public function __construct($data, $graphType, $headers, $title = 'Reporte')
    {
        $this->data = $data;
        $this->graphType = $graphType;
        $this->headers = $headers;
        $this->title = $title;
    }

    public function array(): array
    {
        $rows = [];
        $index = 1;

        foreach ($this->data['detections'] as $detection) {
            $row = [
                $index,
                $detection['service'],
                $detection['area_name'],
                $detection['device_name'],
                is_array($detection['versions']) ? implode(', ', $detection['versions']) : $detection['versions'],
            ];

            if ($this->graphType == 'cptr') {
                foreach ($this->headers as $header) {
                    $row[] = $detection['pest_total_detections'][$header] ?? 0;
                }
            } elseif ($this->graphType == 'cnsm') {
                if (!empty($detection['weekly_consumption'])) {
                    foreach ($this->headers as $header) {
                        $row[] = $detection['weekly_consumption'][$header] ?? 0;
                    }
                } else {
                    $row[] = $detection['consumption_value'] ?? 0;
                }
            }

            $rows[] = $row;
            $index++;
        }

        // Agregar fila de totales
        if (!empty($this->data['detections'])) {
            $totalRow = ['TOTAL GENERAL', '', '', '', ''];
            
            if ($this->graphType == 'cptr') {
                foreach ($this->headers as $header) {
                    $totalRow[] = $this->data['grand_totals'][$header] ?? 0;
                }
            } elseif ($this->graphType == 'cnsm') {
                if (!empty($this->data['grand_totals_weekly'])) {
                    foreach ($this->headers as $header) {
                        $totalRow[] = $this->data['grand_totals_weekly'][$header] ?? 0;
                    }
                } else {
                    $totalRow[] = $this->data['grand_total_consumption'] ?? 0;
                }
            }
            
            $rows[] = $totalRow;
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['#', 'Servicio', 'Área', 'Dispositivo', 'Versión'];
        
        foreach ($this->headers as $header) {
            $headings[] = $header;
        }
        
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data['detections']) + 2; // +2 para encabezado y fila de totales
        
        return [
            // Estilo para encabezados
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            ],
            // Estilo para fila de totales
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  // #
            'B' => 20, // Servicio
            'C' => 20, // Área
            'D' => 25, // Dispositivo
            'E' => 15, // Versión
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Aplicar bordes a toda la tabla
                $lastRow = count($this->data['detections']) + 2;
                $lastColumn = count($this->headers) + 5; // 5 columnas fijas
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumn);
                
                $event->sheet->getStyle("A1:{$columnLetter}{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                
                // Centrar contenido
                $event->sheet->getStyle("A1:{$columnLetter}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
                // Autoajustar anchos de columnas dinámicas
                for ($i = 6; $i <= $lastColumn; $i++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $event->sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
            },
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}