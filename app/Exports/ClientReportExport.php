<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientReportExport implements 
FromCollection, 
WithHeadings, 
WithMapping, 
WithStyles, 
WithColumnFormatting
// Quitamos ShouldAutoSize para controlar nosotros el ancho de las columnas problemáticas
{
    protected $customers;
    protected $metrics;

    public function __construct($customers, array $metrics)
    {
        $this->customers = $customers;
        $this->metrics = $metrics;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function headings(): array
    {
        $headers = [
            'ID del cliente.',
            'Código del cliente.',
            'Nombre comercial.',
            'Razón social.',
            'Teléfono.',
            'Correo.',
            'Fecha de alta.',
            'Fecha de primera orden.',
            'Fecha de última orden.',
            'Tipo de cliente: nuevo o recurrente.'
        ];

        if (in_array('inc_orders_count', $this->metrics)) {
            $headers[] = "Cantidad de\nórdenes en el rango.";
        }

        if (in_array('inc_has_devices', $this->metrics)) {
            $headers[] = "Cuenta con\ndispositivos: sí/no.";
        }

        if (in_array('inc_devices_count', $this->metrics)) {
            $headers[] = "Cantidad de\ndispositivos.";
        }

        if (in_array('inc_device_types', $this->metrics)) {
            $headers[] = 'Tipos de dispositivos.';
        }

        if (in_array('inc_pest_count', $this->metrics)) {
            $headers[] = "Cantidad de\nplagas asociadas.";
        }

        if (in_array('inc_pest_types', $this->metrics)) {
            $headers[] = 'Plagas detectadas.';
        }

        return $headers;
    }

    public function map($customer): array
    {
        $row = [
            $customer->id,
            $customer->code,
            $customer->name,
            $customer->businessname,
            $customer->tel,
            $customer->email,
            $customer->created_at ? 
            ExcelDate::dateTimeToExcel(
                Carbon::parse($customer->created_at)) : null,
            $customer->first_order ? 
            ExcelDate::dateTimeToExcel(
                Carbon::parse($customer->first_order)) : null,
            $customer->last_order ? 
            ExcelDate::dateTimeToExcel(
                Carbon::parse($customer->last_order)) : null,
            $customer->calculated_type === 'new' ? 'Nuevo' : 'Recurrente'
        ];

        if (in_array('inc_orders_count', $this->metrics)) {
            $row[] = $customer->total_orders_in_range;
        }

        if (in_array('inc_has_devices', $this->metrics)) {
            $row[] = ($customer->devices_count > 0) ? 'Sí' : 'No';
        }

        if (in_array('inc_devices_count', $this->metrics)) {
            $row[] = $customer->devices_count;
        }

        if (in_array('inc_device_types', $this->metrics)) {
            $row[] = $customer->device_types ?? '';
        }

        if (in_array('inc_pest_count', $this->metrics)) {
            $row[] = $customer->pest_count;
        }

        if (in_array('inc_pest_types', $this->metrics)) {
            $row[] = $customer->pest_types;
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $headerRange = "A1:{$highestColumn}1";
        $fullRange = "A1:{$highestColumn}{$highestRow}";

        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        $sheet->getRowDimension(1)->setRowHeight(75);

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THICK],
            ],
        ]);

        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($fullRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($fullRange)->getAlignment()->setWrapText(true);

        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF5F5F5');
            }
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        foreach (range('A', $highestColumn) as $column) {
            $title = trim((string) $sheet->getCell("{$column}1")->getValue());
            $dataRange = "{$column}2:{$column}{$highestRow}";
            $columnDimension = $sheet->getColumnDimension($column);

            if ($title === 'ID del cliente.') {
                $columnDimension->setWidth(10);
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif (in_array($title, ['Código del cliente.', 'Teléfono.'], true) || str_contains($title, 'Fecha')) {
                $columnDimension->setWidth(15);
            } elseif ($title === 'Nombre comercial.' || $title === 'Razón social.') {
                $columnDimension->setWidth(55);
            } elseif ($title === 'Correo.') {
                $columnDimension->setWidth(45);
            } elseif (str_contains($title, 'Tipo de cliente')) {
                $columnDimension->setWidth(18);
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($row = 2; $row <= $highestRow; $row++) {
                    $value = trim((string) $sheet->getCell("{$column}{$row}")->getValue());

                    if ($value === 'Nuevo') {
                        $sheet->getStyle("{$column}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF2E7D32'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    } elseif ($value === 'Recurrente') {
                        $sheet->getStyle("{$column}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF1565C0'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                }
            } elseif (str_contains($title, 'Cantidad') || str_contains($title, 'Cuenta con')) {
                $columnDimension->setWidth(20);
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif (str_contains($title, 'Tipos de dispositivos') || str_contains($title, 'Plagas detectadas')) {
                $columnDimension->setWidth(50);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_TOP,
                    ],
                ]);
            }
        }

        return [];
    }

    public function columnFormats(): array
    {
        $formats = [];
        $headers = $this->headings();

        foreach ($headers as $index => $title) {
            if (str_contains($title, 'Fecha')) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                $formats[$column] = 'yyyy-mm-dd';
            }
        }

        return $formats;
    }
}
