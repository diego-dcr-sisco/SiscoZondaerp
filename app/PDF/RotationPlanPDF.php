<?php
namespace App\PDF;


use App\Models\ProductCatalog;
use App\Models\RotationPlan;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

use TCPDF;
use DateTime;

class RotationPlanPDF extends TCPDF
{
    private $margin, $id, $widthPage, $heightPage;

    private $months = [
        'ENERO',
        'FEBRERO',
        'MARZO',
        'ABRIL',
        'MAYO',
        'JUNIO',
        'JULIO',
        'AGOSTO',
        'SEPTIEMBRE',
        'OCTUBRE',
        'NOVIEMBRE',
        'DICIEMBRE'
    ];    

    public function __construct($id)
    {
        parent::__construct();
        $this->margin = 10;
        $this->id = $id;
        $this->widthPage = $this->getPageWidth() - $this->margin;
        $this->heightPage = $this->getPageHeight() - $this->margin;
        $this->SetMargins($this->margin, $this->margin * 2, $this->margin);
        $this->SetAutoPageBreak(true, $this->margin);
    }

    private function watermark()
    {
        $this->SetAlpha(0.1);

        $pageWidth = $this->getPageWidth() + ($this->margin * 2);
        $pageHeight = $this->getPageHeight() + ($this->margin * 2);

        $imageWidth = 220;
        $imageHeight = 300;

        $x = ($pageWidth - $imageWidth) / 2;
        $y = ($pageHeight - $imageHeight) / 2;

        // Añade la imagen
        $this->Image(public_path('images/marcadeagua.png'), $x, $y, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0);
        $this->SetAlpha(1);
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = str_repeat($hex[0], 2) . str_repeat($hex[1], 2) . str_repeat($hex[2], 2);
        }

        $rgb = [
            'red' => hexdec(substr($hex, 0, 2)),
            'green' => hexdec(substr($hex, 2, 2)),
            'blue' => hexdec(substr($hex, 4, 2)),
        ];

        return $rgb;
    }

    private function getMonthsBetweenDates($startDate, $endDate)
    {
        setlocale(LC_TIME, 'es_ES.UTF-8');

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('first day of next month');
        $monthsByYear = [];

        while ($start < $end) {
            $year = $start->format('Y');
            $monthNum = $start->format('m');
            $monthName = strtoupper(strftime('%B', mktime(0, 0, 0, $monthNum, 1)));

            if (!isset($monthsByYear[$year])) {
                $monthsByYear[$year] = [];
            }
            $monthsByYear[$year][] = $monthName;
            $start->modify('+1 month');
        }
        return $monthsByYear;
    }

    private function generateMonthsText($monthsByYear)
    {
        $monthsText = [];

        foreach ($monthsByYear as $year => $months) {
            $months = array_map('strtoupper', $months);
            $monthList = implode(", ", $months);
            $monthsText[] = $monthList . " " . $year;
        }
        return implode(", ", $monthsText);
    }


    private function getYearsByMonth($startDate, $endDate)
    {
        // Convertir las fechas en instancias de Carbon
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        // Verificar que la fecha inicial sea menor o igual a la final
        if ($start->gt($end)) {
            return [];
        }

        $months = array_fill(0, 12, null); // Inicializar el array con 12 posiciones

        // Iterar desde la fecha inicial hasta la fecha final
        while ($start->lte($end)) {
            $months[$start->month - 1] = $start->year; // Restar 1 para usar índice basado en cero
            $start->addMonth(); // Avanzar al siguiente mes
        }

        // Rellenar valores faltantes (null) con el año más reciente
        /*$lastYear = end(array_filter($months)) ?? $start->year;
        foreach ($months as $key => $value) {
            if (is_null($value)) {
                $months[$key] = $lastYear;
            }toUpperCase
        }*/

        return $months;
    }

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->watermark();
    }

    public function Header()
{
    $step = 20;
    $rotation_plan = RotationPlan::find($this->id);

    if ($rotation_plan) {
        $x = $this->GetX();
        $y = $this->GetY() + 5;

        $this->SetFont('helveticaB', '', 18);
        $this->SetTextColor(190, 205, 97);

        $this->setY($y);

        // Ancho máximo: la mitad de la página menos el margen
        $max_width = ($this->widthPage / 2);

        // Mostrar el texto con límite de ancho
        $this->MultiCell($max_width, $step, $rotation_plan->name, 0, 'L');

        // Imagen alineada a la derecha
        $imgX = ($this->widthPage / 2) + ($this->margin * 2);
        $img_width = 80;
        $img_height = 25;
        $imagePath = public_path('images/logo.png');
        $this->Image($imagePath, $imgX, 0, $img_width, $img_height, 'PNG');

        // Ajustar posición después del texto y la imagen
        $this->setXY($x, $this->GetY() + $img_height);
    }
}

    public function Customer()
    {
        $rotation_plan = RotationPlan::find($this->id);
        $customer = $rotation_plan->customer;

        if ($customer) {
            $this->SetTextColor(0, 0, 0);
            $x = $this->GetX();
            $y = $this->GetY() + 2;
            $this->SetXY($x, $y);
            $this->SetFont('helveticaB', '', 9);
            $this->Cell($x, 1, 'Cliente: ' . $customer->name, 0, 1, 'L');
            $this->Ln(1);
            $this->Cell($x, 1, 'Código: ' . $rotation_plan->code, 0, 1, 'L');
            $this->Ln(1);
            $this->Cell($x, 1, 'Fecha de autorización: ' . date('d-m-Y', strtotime($rotation_plan->authorizated_at)), 0, 1, 'L');
            $this->Ln(1);
            $this->Cell($x, 1, 'Duración: ' . date('d-m-Y', strtotime($rotation_plan->start_date)) . ' a ' . date('d-m-Y', strtotime($rotation_plan->end_date)), 0, 1, 'L');
            $this->Ln(1);
            $this->Cell($x, 1, 'Fecha de revisión: ' . date('d-m-Y', strtotime($rotation_plan->updated_at)), 0, 1, 'L');
            $this->Ln(1);
            /*$this->Cell($x, 1, 'Elaboró: ' . '' . ' a ' . date('d-m-Y', strtotime($rotation_plan->enddate)) , 0, 1, 'L');
            $this->Ln(1);*/

            $this->SetFont('helvetica', '', 8);
            $middleX = ($this->widthPage / 2) + ($this->margin * 2);
            //$y = $this->GetY();

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $customer->branch->fiscal_name, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $customer->branch->address, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $customer->branch->colony . ' #' . $customer->branch->zip_code . ', ' . $customer->branch->state . ', ' . $customer->branch->country, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $this->GetY());
            $this->Cell(0, 1, $customer->branch->email, 0, 1, 'C');

            $this->SetFillColor(255, 255, 255);
            $cont = 'Licencia Sanitaria nº : ' . $customer->branch->license_number;
            $nameTel = $customer->branch->name . ' Tel. ' . $customer->branch->phone . " ";
            $this->SetXY($middleX, $this->GetY());
            $this->MultiCell(0, 1, $cont . " " . $nameTel . " ", 0, 'C');

            $this->Ln(8);
        }
    }

    public function Changes()
    {
        $step = 10;
        $cell_height = 6;
        $rotation_plan = RotationPlan::find($this->id);
        $headers = ['FECHA', 'No. REVISIÓN', 'DESCRIPCIÓN DEL CAMBIO'];

        $width = $this->widthPage - $this->margin;
        $width_td = $width / 2;
        $widths = [$width_td / 4, $width_td / 4, $width_td + $width_td / 2];

        if ($rotation_plan) {
            $x = $this->GetX();
            $y = $this->GetY();
            $width = $this->widthPage - $this->margin;
            $this->SetFont('helveticaB', '', 10);
            $this->SetFillColor(118, 172, 220);
            $this->SetXY($x, y: $y);
            $this->Cell($width, $cell_height, 'CAMBIOS', 0, 0, 'L', true);
            $this->setFontSize(9);
            $this->Ln(8);
            $this->SetFillColor(204, 209, 209);

            foreach ($headers as $index => $header) {
                $newX = $this->GetX() + $widths[$index];
                $y = $this->GetY();
                $this->MultiCell($widths[$index], $cell_height, $header, 0, 'L', true);
                $this->SetXY($newX, $y);
            }

            $this->setX($x);
            $this->Ln();

            $this->SetFont('helvetica', '', 9);
            $this->SetFillColor(242, 244, 244);

            foreach ($rotation_plan->changes as $rpc) {
                $newX = $this->GetX();
                $y = $this->GetY();
                $this->MultiCell($widths[0], $cell_height, date('d-m-Y', strtotime($rpc->created_at)) ?? '-', 0, 'L', true);
                $this->SetXY($newX + $widths[0], $y);

                $newX = $this->GetX();
                $this->MultiCell($widths[1], $cell_height, $rpc->no_review ?? '-', 0, 'L', true);
                $this->SetXY($newX + $widths[1], $y);

                $newX = $this->GetX();
                $this->MultiCell($widths[2], $cell_height, $rpc->description ?? '-', 0, 'L', true);
                $this->SetXY($newX + $widths[2], $y);

                $this->setY($this->GetY() + $cell_height + 0.5);
            }
        }

        $this->setX($x);
        $this->Ln(4);
    }

    public function Products()
    {
        $step = 10;
        $cell_height = 6;
        $rotation_plan = RotationPlan::find($this->id);
        $headers = ['PRODUCTO', 'UTILIZACIÓN', 'INGREDIENTE ACTIVO'];
        $productsByColor = [];

        $width = $this->widthPage - $this->margin;
        $width_td = $width / count($headers);

        if ($rotation_plan) {
            $x = $this->GetX();
            $y = $this->GetY();
            $width = $this->widthPage - $this->margin;
            $this->SetFont('helveticaB', '', 10);
            $this->SetFillColor(118, 172, 220);
            $this->SetXY($x, y: $y);
            $this->Cell($width, $cell_height, 'PRODUCTOS', 0, 0, 'L', true);
            $this->Ln(8);
            $this->setFontSize(9);
            $this->SetFillColor(204, 209, 209);

            foreach ($headers as $header) {
                $newX = $this->GetX() + $width_td;
                $y = $this->GetY();
                $this->MultiCell($width_td, $cell_height, $header, 0, 'L', true);
                $this->SetXY($newX, $y);
            }

            $this->setY($this->GetY() + $cell_height + 0.5);
            $this->SetFont('helvetica', '', 9);

            $products = $rotation_plan->products->toArray();

            $productsByColor = collect($products)
                ->groupBy('color')
                ->sortKeys()
                ->toArray();

            foreach ($productsByColor as $indexColor => $productsArray) {
                foreach ($productsArray as $productArr) {
                    $fetched_product = ProductCatalog::find($productArr['product_id']);

                    $newX = $this->GetX();
                    $y = $this->GetY();

                    $rgb = $this->hexToRgb($indexColor);
                    $this->SetFillColor($rgb['red'], $rgb['green'], $rgb['blue']);

                    $this->MultiCell($width_td, $cell_height, $fetched_product->name, 0, 'L', true);
                    $this->SetXY($newX + $width_td, $y);

                    $newX = $this->GetX();
                    $this->MultiCell($width_td, $cell_height, $fetched_product->biocide->group ?? '-', 0, 'L', true);
                    $this->SetXY($newX + $width_td, $y);

                    $newX = $this->GetX();
                    $this->MultiCell($width_td, $cell_height, $fetched_product->active_ingredient ?? '-', 0, 'L', true);
                    $this->SetXY($newX + $width_td, $y);

                    $this->setY($this->GetY() + $cell_height + 0.5);
                }
            }

            $this->setX($x);
            $this->Ln();


            $monthsInYear = $this->getYearsByMonth($rotation_plan->contract->startdate, $rotation_plan->contract->enddate);
            $month_indexs = [];

            foreach ($productsByColor as $indexColor => $productsArray) {
                $month_indexs = [];
                foreach ($productsArray as $productArr) {
                    $month_indexs[] = json_decode($productArr['months']);
                }
                $month_indexs = array_values(array_unique(array_merge(...$month_indexs)));
                $months = [];

                foreach ($month_indexs as $month_index) {
                    $months[] = $this->months[$month_index - 1] . '(' . $monthsInYear[$month_index - 1] . ')';
                }

                $newY = $this->GetY();
                $this->SetY($newY);

                $rgb = $this->hexToRgb($indexColor);
                $this->SetFillColor($rgb['red'], $rgb['green'], $rgb['blue']);
                $this->Rect($x, $newY, $step * 2, 5, 'DF');

                $newX = $x + $step * 2.5;
                $this->setX($newX);
                $this->setTextColor(0, 0, 0);
                $this->SetFont('helvetica', '', 9);
                $this->MultiCell($width - $this->margin*2, 4, 'PRODUCTO(S) POR UTILIZAR ' . implode(', ', $months), 0, 'L');
                $this->Ln(4);
            }
        }
    }

    public function Adc()
    {
        $rotation_plan = RotationPlan::find($this->id);

        if ($rotation_plan) {
            $this->SetTextColor(0, 0, 0);
            $x = $this->GetX();
            $y = $this->GetY() + 3;
            $width = $this->widthPage - $this->margin;
            $this->SetXY($x, $y);
            $this->SetFont('helveticaB', '', 10);
            $this->MultiCell($width, 1,'AVISO IMPORTANTE', 0, 'L');
            $this->Ln(1);
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell($width, 1,$rotation_plan->important_text, 0, 'L');
            $this->Ln();
            $this->SetFont('helveticaB', '', 10);
            $this->MultiCell($width, 1, 'NOTAS', 0, 'L');
            $this->Ln(1);
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell($width, 1,$rotation_plan->notes, 0, 'L');
            $this->Ln();
        }
    }
}