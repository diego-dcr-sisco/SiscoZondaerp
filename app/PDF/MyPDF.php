<?php

namespace App\PDF;

use App\Models\ApplicationArea;
use App\Models\ApplicationMethod;
use App\Models\ControlPoint;
use App\Models\DevicePest;
use App\Models\DeviceStates;
use App\Models\OrderArea;
use App\Models\OrderIncidents;
use App\Models\OrderProduct;
use App\Models\LineBusiness;
use App\Models\Branch;
use App\Models\Order;
use App\Models\FloorPlans;
use App\Models\Device;
use App\Models\OrderName;
use App\Models\PropagateService;
use App\Models\Recommendations;
use App\Models\ServiceDetails;
use App\Models\UserFile;
use App\Models\DeviceProduct;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use TCPDF;

class MyPDF extends TCPDF
{
    private $orderId, $margin, $widthPage, $heightPage, $startX, $startY;
    private $states_route = 'datas/json/Mexico_states.json';


    public function __construct($orderId)
    {
        parent::__construct();
        $margin = 10;
        $this->orderId = $orderId;
        $this->margin = $margin;
        $this->widthPage = $this->getPageWidth() - $margin;
        $this->heightPage = $this->getPageHeight() - $margin;
        $this->SetMargins($margin, $margin * 5, $margin); // Margen izquierdo, margen superior, margen derecho
        $this->SetAutoPageBreak(true, $margin); // Habilitar el ajuste automático de página
    }

    // Añadimos la función para la marca de agua con imagen
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
        $this->Image(public_path('images/zonda_marcadeagua.png'), $x, $y, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0);
        $this->SetAlpha(1);
    }

    private function getNameByKey($key, $data)
    {
        $result = collect($data)->firstWhere('key', $key);
        return $result ? $result['name'] : null; // O puedes retornar un mensaje personalizado
    }


    private function getSignature($signature_base64)
    {
        // Verificar si la firma está vacía
        if (empty($signature_base64)) {
            return null; // o devolver una imagen por defecto
        }

        /* Verificar si es una cadena base64 válida
        if (!preg_match('/^data:image\/(png|jpg|jpeg|gif);base64,/', $signature_base64)) {
            return null;
        }*/

        $signature_base64 = preg_replace('/^data:image\/(png|jpg|jpeg|gif);base64,/', '', $signature_base64);
        $signature = base64_decode($signature_base64);

        // Verificar si la decodificación fue exitosa
        if ($signature === false) {
            return null;
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'signature_') . '.png';

        if (file_put_contents($temp_file, $signature) === false) {
            return null;
        }

        try {
            $image = Image::make($temp_file)->encode('png');
            $width = $image->width();
            $height = $image->height();
            $transparentImage = Image::canvas($width, $height, [0, 0, 0, 0]);

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $pixel = $image->pickColor($x, $y, 'array');
                    if ($pixel[0] > 200 && $pixel[1] > 200 && $pixel[2] > 200) {
                        $transparentImage->pixel([0, 0, 0, 0], $x, $y);
                    } else {
                        $transparentImage->pixel($pixel, $x, $y);
                    }
                }
            }

            $transparentImage->save($temp_file);
            return $temp_file;
        } catch (\Exception $e) {
            // Logear el error si es necesario
            return null;
        }
    }

    protected function isEndPage($margin = 20)
    {
        if ($this->GetY() + $margin > $this->PageBreakTrigger) {
            $this->AddPage();
            return true;
        }
        return false;
    }


    private function get_unit_abbreviation($text)
    {
        if (preg_match('/\((.*?)\)/', $text, $matches)) {
            return $matches[1]; // devuelve lo que está entre paréntesis
        }

        return ''; // o lo que prefieras si no hay paréntesis
    }

    private function lenByString($str)
    {
        $div = 5; // Divisor para la longitud de la cadena
        $len = strlen($str); // Calcula la longitud de la cadena
        if ($div > $len) {
            return 1;
        } else {
            return intdiv($len, $div); // Retorna la división entera entre 5
        }
    }

    private function calculateCellSize($text, $maxWidth, $fontSize = 8, $padding = 2)
    {
        // Limitar recursión y tiempo de procesamiento
        static $recursionDepth = 0;
        $recursionDepth++;

        if ($recursionDepth > 10) { // Límite de seguridad
            return [
                'height' => $fontSize / 2,
                'font_size' => max(6, $fontSize - 1)
            ];
        }

        // Usar el método nativo de TCPDF para mayor precisión y rendimiento
        $textWidth = $this->GetStringWidth($text, 'helvetica', '', $fontSize);
        $availableWidth = $maxWidth - $padding;

        // Si el texto cabe en el ancho disponible
        if ($textWidth <= $availableWidth) {
            $recursionDepth = 0; // Resetear contador
            return [
                'height' => $fontSize / 2,
                'font_size' => $fontSize
            ];
        }

        // Calcular relación de escalado sin recursión
        $scaleFactor = $availableWidth / $textWidth;
        $adjustedFontSize = max(6, $fontSize * $scaleFactor * 0.9); // Factor de ajuste

        // Calcular líneas aproximadas
        $approxLines = ceil($textWidth / $availableWidth);

        $recursionDepth = 0; // Resetear contador
        return [
            'height' => ($approxLines * ($adjustedFontSize / 2)) + 1,
            'font_size' => $adjustedFontSize
        ];
    }

    private function getCWidth($fontSize)
    {
        // Aproximación: 1pt = 0.3528mm, ancho promedio de caracter (depende de fuente)
        return ($fontSize * 0.3528) * 0.6; // Factor de ajuste empírico
    }

    protected function cleanHTML($html)
    {
        // Procesamiento de imágenes
        $html = preg_replace_callback('/<img([^>]*)>/i', function ($matches) {
            $attrs = $matches[1];
            $attrs = preg_replace('/(width|height|style|class)="[^"]*"/i', '', $attrs);
            $attrs .= ' style="max-width: 100%; height: auto;"';
            return '<img' . $attrs . '>';
        }, $html);

        // Eliminar etiquetas peligrosas
        $html = preg_replace('/<(script|style|iframe|form|input|textarea)[^>]*>.*?<\/\1>/si', '', $html);

        // Eliminar atributos de estilo y clase
        $html = preg_replace('/(<[^>]+) style="[^"]*"/i', '$1', $html);
        $html = preg_replace('/(<[^>]+) class="[^"]*"/i', '$1', $html);

        // Convertir divs y spans a párrafos
        //$html = preg_replace('/<(\/)?(div|span)>/i', '<$1p>', $html);

        // Eliminar etiquetas vacías
        $html = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/i', '', $html);

        // Limpieza de <br> innecesarios
        $html = preg_replace('/(<\/\w+>)<br\s*\/?>/i', '$1', $html);
        $html = preg_replace('/<br\s*\/?>(<\w+>)/i', '$1', $html);
        $html = preg_replace('/(<br\s*\/?>){2,}/i', '<br>', $html);
        $html = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $html);
        $html = preg_replace_callback('/<(\w+)[^>]*>(.*?)<\/\1>/is', function ($matches) {
            $content = preg_replace('/<br\s*\/?>/i', '', $matches[2]);
            return "<{$matches[1]}>{$content}</{$matches[1]}>";
        }, $html);

        // Formateo especial para texto estructurado
        //$html = $this->formatStructuredText($html);

        // Asegurar HTML bien formado
        return '<div style="font-family: helvetica; font-size: 8pt;">' . $html . '</div>';
    }

    protected function formatStructuredText($text)
    {
        // Normalizar saltos de línea
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Patrones para formato especial
        $patterns = [
            // Unir títulos (líneas que terminan en :) con su contenido
            '/([^\n]+:)\s*\n\s*/' => '$1 ',

            // Unir elementos de lista separados por saltos
            '/([A-ZÁÉÍÓÚ][A-ZÁÉÍÓÚa-zéíóú]+,?)\s*\n\s*([A-ZÁÉÍÓÚ])/' => '$1, $2',

            // Manejar áreas de aplicación especiales
            '/(ÁREAS DE APLICACIÓN:\s*)\n(.+?)\n(.+?)(?=\n|$)/s' => "$1\n$2\n$3",

            // Limpiar espacios alrededor de comas
            '/\s*,\s*/' => ', ',

            // Eliminar espacios múltiples
            '/\s+/' => ' ',

            // Limpiar saltos de línea múltiples
            '/\n+/' => "\n"
        ];

        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return trim($text);
    }

    // Sobrescribimos la función AddPage para incluir la marca de agua
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->watermark();
    }

    public function Header()
    {
        $order = Order::find($this->orderId);
        $step = 20;

        if ($order) {
            $parts = explode('-', $order->folio);
            $num = $parts[count($parts) - 1];
            $x = $this->GetX();
            $y = $this->GetY() + $step;
            $this->SetFont('helveticaB', '', 15);
            $this->SetTextColor(190, 205, 97);
            $this->Cell($x, $y, 'Certificado de Servicio Nº ' . $num, 0, 1, 'L');

            $img_width = 80;
            $img_height = 25;
            $imagePath = public_path('images/logo.png');
            $this->Image($imagePath, $this->widthPage - $img_width, 0, $img_width, $img_height, 'PNG');

            $this->SetFontSize(8);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('helvetica');
            $this->Cell($x, 1, 'CONTROL DE PLAGAS', 0, 1, 'L');

            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($x, $y);
            $this->Cell($x, 1, 'Fecha de ejecución: ' . date('d-m-Y', strtotime($order->programmed_date)), 0, 1, 'L');

            $this->SetFont('helvetica');
            $middleX = ($this->widthPage / 2) + ($this->margin * 2);
            $y = $this->GetY() - $step / 4;

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $order->customer->branch->fiscal_name, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $order->customer->branch->address, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $y);
            $this->Cell(0, 1, $order->customer->branch->colony . ' #' . $order->customer->branch->zip_code . ', ' . $order->customer->branch->state . ', ' . $order->customer->branch->country, 0, 1, 'C');
            $y = $this->GetY();

            $this->SetXY($middleX, $this->GetY());
            $this->Cell(0, 1, $order->customer->branch->email, 0, 1, 'C');

            $this->SetFillColor(255, 255, 255);
            $cont = 'Licencia Sanitaria nº : ' . $order->customer->branch->license_number;
            $nameTel = $order->customer->branch->name . ' Tel. ' . $order->customer->branch->phone . " ";
            $this->SetXY($middleX, $this->GetY());
            $this->MultiCell(0, 1, $cont . " " . $nameTel . " ", 0, 'C');

            $y = $this->GetY();
            $this->SetXY($x, $y);

            $this->startY = $y;

            //dd('Y: ' . $y . ' Max Y: ' . $this->getPageHeight());
        } else {
            throw new Exception("Order not found.");
        }
    }

    public function Customer()
    {
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $order = Order::find($this->orderId);
        $step = 20;

        $x = $this->GetX();
        $y = $this->GetY();
        $width = ($this->widthPage / 2) - $this->margin;
        $middleX = ($this->widthPage / 2) + $this->margin;

        $this->SetFont('helvetica', '', 9);

        $this->SetXY($x, $y);
        $this->Ln();
        $this->SetFillColor(130, 178, 221);
        $this->Cell($width, 5, 'FECHA Y HORARIO', 0, 0, 'L', true);
        $this->Ln();
        $this->SetFontSize(8);
        $this->Cell($width, 5, 'Fecha de Comienzo: ' . Carbon::createFromFormat('Y-m-d', $order->programmed_date)->format('d/m/Y') . ' - ' . Carbon::createFromFormat('H:i:s', $order->start_time ?? '00:00:00')->format('H:i'), 0, 0, 'L');
        $this->Ln(4);
        $this->Cell($width, 5, 'Fecha de Finalizacion: ' . ($order->completed_date ? Carbon::createFromFormat('Y-m-d', $order->completed_date)->format('d/m/Y') : '') . ' ' . Carbon::createFromFormat('H:i:s', $order->end_time ?? '00:00:00')->format('H:i'), 0, 0, 'L');

        $this->Ln(6);
        $y = $this->GetY();
        $this->SetFont('helvetica', '', 9);
        $this->SetFillColor(130, 178, 221);

        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'DATOS DEL CLIENTE Y SU SEDE', 0, 0, 'L', true);

        $this->SetXY($middleX, y: $y);
        $this->Cell($width, 5, 'PRESTA EL SERVICIO', 0, 0, 'L', true);
        $this->Ln();

        $this->SetFontSize(8);

        $y = $this->GetY();
        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'Razón Social: ' . $order->customer->tax_name, 0, 0, 'L');

        $this->SetXY($middleX, y: $y);
        $this->Cell($width, 5, $order->customer->branch->fiscal_name, 0, 0, 'L');
        $this->Ln(4);

        $y = $this->GetY();
        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'Sede: ' . $order->customer->name, 0, 0, 'L');

        $this->SetXY($middleX, y: $y);
        $this->Cell($width, 5, $order->customer->branch->address, 0, 0, 'L');
        $this->Ln(4);

        $y = $this->GetY();

        // Dirección
        $this->SetXY($x, $y);
        $this->MultiCell($width, 5, 'Dirección: ' . $order->customer->address, 0, 'L');

        // Mantener la misma altura inicial para la segunda columna
        $this->SetXY($middleX, $y);
        $this->MultiCell(
            $width,
            5,
            $order->customer->branch->colony . ' ' .
            $order->customer->zip_code . ' ' .
            $order->customer->branch->city . ' ' .
            $this->getNameByKey($order->customer->state, $states),
            0,
            'L'
        );

        $y = $this->GetY();
        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'Municipio: ' . $order->customer->city, 0, 0, 'L');

        $this->SetXY($middleX, $y);
        $this->SetFont('helveticaB');
        $this->Cell($width, 5, 'Licencia Sanitaria ROESB con nº ' . $order->customer->branch->license_number, 0, 0, 'L');
        $this->SetFont('helvetica');
        $this->Ln(4);

        $this->Cell($width, 5, 'Estado/Entidad: ' . $this->getNameByKey($order->customer->state, $states), 0, 0, 'L');
        $this->Ln(4);
        $this->Cell($width, 5, 'Tel: ' . $order->customer->phone, 0, 0, 'L');

        $this->Ln(6);
        $y = $this->GetY();
        $this->SetXY($x, $y);
    }

    public function Services()
    {
        $order = Order::find($this->orderId);
        $step = 10;
        $x = $this->GetX();
        $y = $this->GetY();
        $width = $this->widthPage - $this->margin;
        $this->SetFont('helvetica', '', 9);

        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'TRATAMIENTOS', 0, 0, 'L', true);
        $this->Ln();

        if ($order->services->isNotEmpty()) {
            foreach ($order->services as $service) {
                $propagate = PropagateService::where('order_id', $order->id)->where('service_id', $service->id)->where('setting_id', $order->setting_id)->where('contract_id', $order->contract_id)->first();
                $this->Ln(2);
                $newY = $this->GetY();
                $this->SetXY($x + $step / 1.5, $newY);
                $this->SetFillColor(130, 178, 221);
                $this->Rect($x, $newY, 4, 4, 'F');

                $this->setTextColor(33, 97, 140);
                $this->SetFont('helveticaB', '', 10);
                $this->Cell(($width - $this->margin) / 2, 4, $service->name, 0, 0, 'L');
                $this->setTextColor(0, 0, 0);
                $this->Ln(6);

                $newY = $this->GetY();
                $this->SetXY($x, $newY);
                $this->SetFont('helvetica', '', 8);
                $this->writeHTML($this->cleanHTML($propagate->text) ?? 'S/A', true, false, true, false, '');

                $newY = $this->GetY();
                $this->SetXY($x, $newY);
            }
        }

        $this->Ln(6);
        $y = $this->GetY();
        $this->SetXY($x, y: $y);
    }

    public function Products()
    {
        $headers = ['Materia activa', 'No Registro', 'Plazo seguridad', 'Método de aplicación', 'Dosificación', 'Consumo', 'Lote'];
        $order_products = OrderProduct::where('order_id', $this->orderId)->get();
        $step = 10;
        $startX = $this->GetX();
        $y = $this->GetY();
        $width = $this->widthPage - $this->margin;
        $width_td = $width / count($headers);

        $this->SetFont('helvetica', '', 9);

        $this->SetXY($startX, y: $y);
        $this->Cell($width, 5, 'PRODUCTOS UTILIZADOS', 0, 0, 'L', true);
        $this->Ln();

        $this->SetFontSize(8);
        $y = $this->GetY();
        $this->SetY($y);

        if ($order_products->isNotEmpty()) {
            $this->SetFillColor(202, 207, 210);
            $this->Ln(2);
            foreach ($headers as $header) {
                $x = $this->GetX() + $width_td;
                $y = $this->GetY();
                $this->MultiCell($width_td, 8, $header, 0, 'L', true);
                $this->SetXY($x, $y);
            }

            $y = $this->GetY() + $step;
            $this->SetXY($startX, y: $y);
            $this->SetFillColor(242, 243, 244);
            foreach ($order_products as $order_product) {
                // Guardar la posición inicial
                $step = 10; // Reduced step for minimal row height
                $x = $this->GetX();
                $y = $this->GetY();
                $dosage = $order_product->dosage ?? $order_product->product->dosage ?? '-';
                $metric = $order_product->metric ? $order_product->metric : $order_product->product->metric;
                $active_name = $order_product->product->active_ingredient ?? $order_product->product->name;

                $activeNameSize = $this->calculateCellSize(
                    $active_name,
                    $width_td,
                    7 // Tamaño de fuente base
                );

                $this->setFontSize($activeNameSize['font_size']);
                $step = $activeNameSize['height'] + 1;

                $this->MultiCell($width_td, $step, $active_name, 0, 'L', true);
                $this->SetXY($x + $width_td, $y); // Mover el cursor a la derecha para la siguiente celda
                //$this->setFontSize(8);

                $this->setFontSize(8);
                $x = $this->GetX(); // Actualizar la posición X después de mover
                $this->MultiCell($width_td, $step, $order_product->product->register_number ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                $x = $this->GetX();
                $this->MultiCell($width_td, $step, $order_product->product->safety_period ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                $x = $this->GetX();
                $this->MultiCell($width_td, $step, $order_product->appMethod->name ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                $x = $this->GetX();
                $this->MultiCell($width_td, $step, $dosage ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                $x = $this->GetX();
                $this->MultiCell($width_td, $step, (number_format($order_product->amount, 2) ?? '-') . ' ' . ($metric->value ?? ''), 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                $x = $this->GetX();
                $this->MultiCell($width_td, $step, $order_product->lot->registration_number ?? $order_product->possible_lot ?? '-', 0, 'L', true);

                // Saltar a la siguiente fila
                $this->Ln(2);
            }
        } else {
            $y = $this->GetY();
            $this->SetXY($startX, $y);
            $this->SetFont('helveticaB', '', 8);
            $this->Cell(($width - $this->margin) / 2, 4, 'Sin productos', 0, 0, 'L');
        }
        $this->Ln(6);
        $y = $this->GetY();
        $this->SetXY($startX, y: $y);
    }


    private function calculateMultiCellHeight($text, $maxWidth, $fontSize = 8)
    {
        // Limpia el texto de saltos de línea innecesarios
        $cleanText = preg_replace('/\s+/', ' ', trim($text));

        // Calcula el ancho aproximado del texto en unidades PDF
        $textWidth = $this->GetStringWidth($cleanText);

        // Calcula cuántas líneas necesitará el MultiCell
        $numLines = ceil($textWidth / $maxWidth);

        // Altura por línea (empíricamente 5pt por línea funciona bien con font size 8)
        $lineHeight = max(5, $fontSize * 0.6);

        // Altura total necesaria
        $totalHeight = $numLines * $lineHeight;

        // Margen adicional de seguridad
        return $totalHeight + 2;
    }


    public function Devices()
    {
        $headers = ['Zona', 'Código', 'Producto y consumo'];
        $order = Order::find($this->orderId);
        $services = $order->services;
        $floorplans = FloorPlans::where('customer_id', $order->customer->id)
            ->whereIn('service_id', $services->pluck('id')->toArray())
            ->get();

        $step = 10; // Reduced step for minimal row height
        $startX = $this->GetX();
        $startY = $this->GetY();
        $width = $this->widthPage - $this->margin;

        $this->SetFont('helvetica', '', 9);
        $this->SetXY($startX, $startY);
        $this->SetFillColor(130, 178, 221);
        $this->Cell($width, 5, 'REVISION DE DISPOSITIVOS DE CONTROL', 0, 0, 'L', true);
        $this->Ln();
        $this->setFontSize(8);

        if ($floorplans->isNotEmpty()) {
            $y = $this->GetY();
            $this->SetY($y);
            $this->Cell($width, 5, 'Sede: ' . $order->customer->name, 0, 0, 'L');
            $this->Ln();

            foreach ($floorplans as $floorplan) {
                $version = $floorplan->versionByDate($order->programmed_date);
                if (!$version)
                    continue;

                $devices = $floorplan->devices($version)->orderBy('itemnumber', 'asc')->get();
                if ($devices->isEmpty())
                    continue;

                $y = $this->GetY();
                if ($y > ($this->PageBreakTrigger - 50)) {
                    $this->AddPage();
                    $y = $this->GetY();
                }

                $this->SetFont('helveticaB', '', 8);
                $this->SetY($y);
                $this->Cell($width, 5, 'Plano: ' . $floorplan->filename, 0, 0, 'L');
                $this->Ln(2);

                $control_types = array_unique($devices->pluck('type_control_point_id')->toArray());
                $points = ControlPoint::whereIn('id', $control_types)->get();

                foreach ($points as $point) {
                    $step = 10; // Reduced step for minimal row height
                    // Agrega si el punto es diferente de CEBO
                    if ($point->id != 16) {
                        $update_headers = array_merge($headers, ['Valor revisión']);
                    } else {
                        $update_headers = $headers;
                    }

                    $questions = $point->questions()->get();
                    $new_headers = array_merge($update_headers, $questions->pluck('question')->toArray());
                    if (isset($point)) {
                        $selected_devices = $devices->filter(fn($device) => $device->type_control_point_id == $point->id);
                    } else {
                        throw new Exception("Variable 'point' is not defined.");
                    }

                    if ($selected_devices->isEmpty())
                        continue;

                    $y = $this->GetY() + $step / 4;
                    if ($y > ($this->PageBreakTrigger - 50)) {
                        $this->AddPage();
                        $y = $this->GetY();
                    }

                    $this->SetFont('helveticaB');
                    $this->SetY($y);
                    $this->Cell($width, 5, 'Punto de control: ' . $point->name, 0, 0, 'L');
                    $this->Ln();
                    $this->SetFont('helvetica');

                    $width_td = $width / count($new_headers);
                    $y = $this->GetY() + $step / 4;

                    // Verificar espacio para encabezados
                    if ($y > ($this->PageBreakTrigger - 20)) {
                        $this->AddPage();
                        $y = $this->GetY();
                    }

                    $this->SetY($y);
                    $this->SetFillColor(202, 207, 210);

                    $x = $startX;
                    foreach ($new_headers as $header) {
                        $this->MultiCell($width_td, $step, $header, 0, 'C', true);
                        $x = $x + $width_td;
                        $this->SetXY($x, $y);
                    }

                    $this->Ln(); // Minimal row spacing


                    foreach ($selected_devices as $device) {
                        $step = 10; // Reduced step for minimal row height
                        $y = $this->GetY() + $step / 4;
                        if ($y > ($this->PageBreakTrigger - 20)) {
                            $this->AddPage();
                            $y = $this->GetY();
                        }

                        $this->SetXY($startX, $y);
                        $this->SetFillColor(242, 243, 244);

                        // Procesar datos del dispositivo
                        $reviews = [];
                        $product_name = '';
                        $pest_reviews = DevicePest::where('device_id', $device->id)
                            ->where('order_id', $this->orderId)
                            ->get();

                        foreach ($pest_reviews as $pest_review) {
                            $reviews[] = "({$pest_review->total}) {$pest_review->pest->name}";
                        }

                        $device_state = $device->states($this->orderId);
                        $product_device = DeviceProduct::where('device_id', $device->id)
                            ->where('order_id', $this->orderId)
                            ->first();

                        // Procesamiento consistente de datos
                        $product_name = $product_device
                            ? $product_device->product->name . ' (' . $product_device->quantity . ' ' .
                            $this->get_unit_abbreviation($product_device->product->metric->value) . ')'
                            : '-';

                        $text = !empty($reviews) ? implode(', ', $reviews) : '-';

                        // Calcular altura necesaria para el texto más largo (product_name o text)
                        $longestText = (strlen($product_name) > strlen($text)) ? $product_name : $text;
                        $step = $this->calculateMultiCellHeight($longestText, $width_td, 7);

                        // Verificar espacio en página
                        $y = $this->GetY();
                        if (($y + $step) > ($this->PageBreakTrigger - 20)) {
                            $this->AddPage();
                            $y = $this->GetY();
                        }

                        // Configurar altura de fila
                        $this->SetXY($startX, $y);

                        $x = $startX;
                        $this->SetXY($x, $y);
                        $this->MultiCell($width_td, $step, $device->applicationArea->name ?? '-', 0, 'C', true);
                        $x += $width_td;

                        $this->SetXY($x, $y);
                        $this->MultiCell($width_td, $step, $device->code, 0, 'C', true);
                        $x += $width_td;

                        $this->SetXY($x, $y);
                        $this->MultiCell($width_td, $step, $product_name, 0, 'L', true);
                        $x += $width_td;

                        // Plagas encontradas en dispositivos (font size dinamico )

                        if ($device->controlPoint->id != 16) {
                            $this->SetXY($x, $y);
                            $this->MultiCell($width_td, $step, $text, 0, 'C', true);
                            $x += $width_td;
                        }
                        
                        $observation = $device_state->observations ?? null;

                        // inicio de preguntas de dispositivos        
                        foreach ($questions as $question) {
                            $incident = OrderIncidents::where('order_id', $this->orderId)
                                ->where('device_id', $device->id)
                                ->where('question_id', $question->id)
                                ->first();
                                
                             if (!$observation) {
                                $observation = OrderIncidents::where('order_id', $this->orderId)
                                    ->where('device_id', $device->id)
                                    ->whereIn('question_id', [33, 34, 35])
                                    ->first()
                                    ->answer ?? null;
                            }

                            $this->setFontSize(8);
                            $this->SetXY($x, $y);
                            $this->MultiCell($width_td, $step, $incident->answer ?? '-', 0, 'C', true);
                            $x += $width_td;
                        }
                        
                        // Verificar si hay observaciones válidas para mostrar
                        if ($observation != null && $observation != '' && $observation != '-') {
                            $this->setFontSize(8);
                            $this->Ln(2);

                            // Procesar el texto de observaciones
                            $obsText = ($device->controlPoint->id == 16)
                                ? $observation . ($pest_text != null ? ' Plagas: ' . $pest_text : '')
                                : $observation;

                            $this->MultiCell($width, 6, 'Observaciones: ' . $obsText, 0, 'L', true);
                            $this->Ln(2);
                        } elseif ($device->controlPoint->id == 16 && $pest_text != null) {
                            // Solo mostrar plagas si no es el caso del guión
                            $this->setFontSize(8);
                            $this->Ln(2);
                            $this->MultiCell($width, 6, 'Observaciones: ' . $pest_text, 0, 'L', true);
                            $this->Ln(2);
                        }

                        // $rowBreak = $length/100;
                    }
                }
            }
        } else {
            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($startX, $y);
            $this->Cell($width, 5, 'Sin revisiones de dispositivos', 0, 0, 'L');
            $this->Ln();
        }
        $this->Ln(2);
    }

    public function Notes()
    {
        $order = Order::find($this->orderId);
        $step = 10;
        $x = $this->GetX();
        $y = $this->GetY();
        $width = $this->widthPage - $this->margin;
        $this->SetFont('helvetica', '', 9);

        $this->SetXY($x, y: $y);
        $this->SetFillColor(130, 178, 221);
        $this->Cell($width, 5, 'NOTAS DEL CLIENTE', 0, 0, 'L', true);
        $this->Ln();

        $newY = $this->GetY();
        $this->SetXY($x, $newY);
        $this->SetFont('helvetica', '', 9);
        $this->writeHTML($order->notes ?? 'Sin notas', true, false, true, false, '');
        $this->Ln(4);


        $newY = $this->GetY();
        $this->SetXY($x, $newY);
    }

    public function Recommendations()
    {
        $order = Order::find($this->orderId);
        $recommendations = $order->reportRecommendations;
        $step = 5;
        $x = $this->GetX();
        $y = $this->GetY();
        $width = $this->widthPage - $this->margin;

        $this->SetFont('helvetica', '', 9);
        $this->SetFillColor(130, 178, 221);
        $this->SetXY($x, y: $y);
        $this->Cell($width, 5, 'RECOMENDACIONES', 0, 0, 'L', true);
        $this->Ln();
        $this->setFontSize(8);

        if (!$recommendations->isEmpty()) {
            if ($recommendations[0]->recommendation_id != null) {
                $recommendations = Recommendations::whereIn('id', $recommendations->pluck('recommendation_id'))->get();
                foreach ($recommendations as $recommendation) {
                    $y = $this->GetY();
                    $this->SetXY($x, $y);
                    $this->MultiCell(
                        $width,
                        5,
                        $recommendation->description,
                        0,
                        'L',
                        false
                    );
                    $this->Ln(2);
                }
            } else {
                $newY = $this->GetY();
                $this->SetXY($x, $newY);
                $this->SetFont('helvetica', '', 9);
                $this->writeHTML($recommendations[0]->recommendation_text ?? 'S/A', true, false, true, false, '');
                $this->Ln(10);
            }
        } else {
            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($x, y: $y);
            $this->Cell($width, 5, 'Sin recomendaciones', 0, 0, 'L');
            $this->Ln();
        }

        $this->Ln(10);
    }

    public function Signature()
    {
        $order = Order::find($this->orderId);
        $width = ($this->widthPage - $this->margin) / 2;
        $step = 10;

        // Verificar espacio en página
        if ($this->GetY() + $step * 4 > $this->heightPage) {
            $this->AddPage();
        }

        $x = $this->GetX();
        $y = $this->GetY() + $step;

        $this->SetXY($x, $y);

        // Configuración de imágenes
        $img_width = 60;
        $img_height = 25;

        // Firma del cliente
        $signature = $order->customer_signature;

        if (empty($signature)) {
            $found_orders = Order::where('customer_id', $order->customer_id)
                ->whereNotNull('customer_signature')
                ->get();
            $signature = $found_orders->first()->customer_signature ?? '';
        }

        // Mostrar firma del cliente
        if (!empty($signature)) {
            $path = $this->getSignature($signature);
            $imgX = $x + ($img_width / 3);
            $this->Image($path, $imgX, $y - $step, $img_width, $img_height, 'PNG');
        }

        // Firma del técnico
        $technician = $order->technicians()->first();
        $techSignaturePath = '';

        if ($technician) {
            $userfile = UserFile::where('user_id', $technician->user_id)
                ->where('filename_id', 15)
                ->first();

            if ($userfile && !empty($userfile->path)) {
                // FORMA CORRECTA de obtener la ruta en Laravel
                $techSignaturePath = storage_path('app/public/' . ltrim($userfile->path, '/'));
            }
        }

        // Mostrar firma del técnico
        $imgX = $x + $width;
        if (!empty($techSignaturePath) && file_exists($techSignaturePath)) {
            $this->Image($techSignaturePath, $imgX, $y - $step, $img_width, $img_height, 'PNG');
        }

        // Textos debajo de las firmas
        $y += $step * 2;
        $this->SetXY($x, $y);
        $this->Cell($width, 5, 'Recibí del cliente: ' . $order->signature_name, 0, 0, 'C');
        $this->Cell($width, 5, 'Nombre y firma del técnico aplicador', 0, 0, 'C');

        $y += $step / 2;
        $this->SetXY($x, $y);
        $this->Cell($width, 5, $order->customer->name ?? '', 0, 0, 'C');
        $this->Cell($width, 5, $technician->user->name ?? '', 0, 0, 'C');

        $y += $step / 2;
        $this->SetXY($x, $y);
        $this->Cell($width, 5, 'RFC: ' . ($order->customer->rfc ?? ''), 0, 0, 'C');
        $this->Cell($width, 5, 'RFC: ' . ($technician->rfc ?? ''), 0, 0, 'C');
    }

    public function Footer()
    {
        $this->SetY($this->margin * -1);
        $this->SetAutoPageBreak(true, 15);
        $this->SetFontSize(8);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 1, 'SISCOPLAGAS', 0, false, 'C');
        $this->Cell(0, 1, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'R');
    }

    public function agregarContenido()
    {
        $this->SetY(45);
    }
}
