<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
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
WithColumnFormatting, 
ShouldAutoSize
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
            $headers[] = 'Cantidad de órdenes en el rango.';
        }

        if (in_array('inc_has_devices', $this->metrics)) {
            $headers[] = 'Cuenta con dispositivos: sí/no.';
        }

        if (in_array('inc_devices_count', $this->metrics)) {
            $headers[] = 'Cantidad de dispositivos.';
        }

        if (in_array('inc_device_types', $this->metrics)) {
            $headers[] = 'Tipos de dispositivos.';
        }

        if (in_array('inc_pest_count', $this->metrics)) {
            $headers[] = 'Cantidad de plagas asociadas.';
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
            $customer->created_at ? ExcelDate::dateTimeToExcel(Carbon::parse($customer->created_at)) : null,
            $customer->first_order ? ExcelDate::dateTimeToExcel(Carbon::parse($customer->first_order)) : null,
            $customer->last_order ? ExcelDate::dateTimeToExcel(Carbon::parse($customer->last_order)) : null,
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
        $fullRange = "A1:{$highestColumn}{$highestRow}";
        $headerRange = "A1:{$highestColumn}1";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
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

        $sheet->setAutoFilter($headerRange);
        $sheet->freezePane('A2');

        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(40);

        $deviceTypeColumn = null;
        foreach (range('A', $highestColumn) as $column) {
            $cellValue = $sheet->getCell("{$column}1")->getValue();
            if ($cellValue === 'Tipos de dispositivos.') {
                $deviceTypeColumn = $column;
                break;
            }
        }

        if ($deviceTypeColumn !== null) {
            $sheet->getColumnDimension($deviceTypeColumn)->setWidth(30);
        }

        $numericColumns = ['A'];
        foreach (range('A', $highestColumn) as $column) {
            $title = $sheet->getCell("{$column}1")->getValue();
            if (in_array($title, ['Cantidad de órdenes en el rango.', 'Cantidad de dispositivos.', 'Cantidad de plagas asociadas.'])) {
                $numericColumns[] = $column;
            }

            if ($title === 'Correo.') {
                $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
        }

        foreach ($numericColumns as $column) {
            $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $tipoClienteColumn = 'J';
        for ($row = 2; $row <= $highestRow; $row++) {
            $cell = $sheet->getCell("{$tipoClienteColumn}{$row}");
            $value = trim((string) $cell->getValue());

            if ($value === 'Nuevo') {
                $sheet->getStyle("{$tipoClienteColumn}{$row}")->applyFromArray([
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E7D32']],
                ]);
            }

            if ($value === 'Recurrente') {
                $sheet->getStyle("{$tipoClienteColumn}{$row}")->applyFromArray([
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1565C0']],
                ]);
            }
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'I' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
        ];
    }
}
