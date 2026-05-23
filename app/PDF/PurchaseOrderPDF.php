<?php
namespace App\PDF;

use App\Models\PurchaseRequisition;
use Exception;
use TCPDF;

class PurchaseOrderPDF extends TCPDF
{
    private $orderId, $margin, $widthPage, $heightPage, $startX, $startY;

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

        $imageWidth = 110 / 1.5;
        $imageHeight = 150 / 1.5;

        $x = $this->getPageWidth() - $imageWidth - $this->margin; // Margen derecho
        $y = $this->getPageHeight() - $imageHeight - $this->margin; // Margen inferior

        // Añade la imagen
        $this->Image(public_path('images/marcadeagua.png'), $x, $y, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0);
        $this->SetAlpha(1);
    }

    private function isEndPage()
    {
        if ($this->GetY() + 20 > $this->PageBreakTrigger) {
            $this->AddPage();
        }
    }

    // Sobrescribimos la función AddPage para incluir la marca de agua
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->watermark();
    }

    public function Header()
    {
        $order = PurchaseRequisition::find($this->orderId);
        $step = 20;
        $width = $this->widthPage - $this->margin;

        if ($order) {
            $imagePath_line = public_path('images/LineaAzul.png');
            $this->Image($imagePath_line, 15, 0, 0, 0, 'PNG');

            // Logo y folio de la orden
            $x = $this->GetX();
            $y = $this->GetY() + $step + 40;
            $this->SetFont('helveticaB', '', 15);
            $this->SetTextColor(190, 205, 97);
            $this->Cell($x, $y, 'Orden de Compra Nº ' . preg_replace('/\D/', '', $order->folio), 0, 1, 'L');

            $img_width = 80;
            $img_height = 25;
            $imagePath = public_path('images/logo.png');
            $this->Image($imagePath, $x - 5, 0, $img_width, $img_height, 'PNG');

            // Datos de la orden 
            $y = $this->GetY() - 25;
            $this->SetXY($x, $y);
            $this->SetFontSize(8);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('helveticaB');
            $this->Cell($x, 1, 'Fecha de emisión: ' . date('d-m-Y'), 0, 1, 'L');

            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($x, $y);
            $this->Cell($x, 1, 'Fecha de creación: ' . date('d-m-Y', strtotime($order->created_at)), 0, 1, 'L');

            $y = $row = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($x, $y);
            $this->Cell($x, 1, 'Solicitante: ' . $order->user->name, 0, 1, 'L');

            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($x, $y);
            $this->Cell($x, 1, 'Departamento de emisión: ' . $order->user->workDepartment->name, 0, 1, 'L');

            $y = $row;
            $this->SetFont('helveticaB');
            $this->SetXY($this->widthPage - 100, $y);
            $this->Cell(100, 1, 'Empresa Destino: ' . $order->customer->name, 0, 1, 'R');

            $y = $this->GetY();
            $this->SetFont('helveticaB');
            $this->SetXY($this->widthPage - 100, $y);
            $this->Cell(100, 1, $order->customer->address, 0, 1, 'R');
            $this->Ln(10);

            // Observaciones de la orden
            $y = $this->GetY();
            $this->SetFont('helvetica', '', 9);
            $this->SetXY($x, $y);
            $this->SetFillColor(130, 178, 221);
            $this->Cell($width, 5, ' Observaciones', 0, 0, 'L', true);
            $this->Ln();
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell($this->widthPage - $this->margin, 0, $order->observations, 0, 'L');
            $this->Ln();

            $y = $this->GetY() + 200;
            $this->SetXY($x, $y);
        } else {
            throw new Exception("Order not found.");
        }
    }

    public function Products()
    {
        $headers = ['Cantidad', 'Producto', 'Proveedor', 'Precio Unitario', 'Total'];
        $order = PurchaseRequisition::find($this->orderId);
        $order_products = $order->products;
        $step = 10;
        $startX = $this->GetX();
        $y = $this->GetY() + 35;
        $width = $this->widthPage - $this->margin;
        $width_td = $width / (count($headers) - 0.5); // Adjust width for 'Cantidad' column

        $total_order = $total_product = 0;

        $this->SetFont('helvetica', '', 9);

        $this->SetXY($startX, $y);
        $this->SetFillColor(130, 178, 221);
        $this->Cell($width, 5, 'PRODUCTOS', 0, 0, 'L', true);
        $this->Ln();

        $this->SetFontSize(8);
        $y = $this->GetY();
        $this->SetY($y);

        if ($order_products->isNotEmpty()) {
            $this->SetFillColor(202, 207, 210);
            $this->Ln(2);
            foreach ($headers as $index => $header) {
                $x = $this->GetX() + ($index == 0 ? $width_td / 2 : $width_td); // Adjust width for 'Cantidad' column
                $y = $this->GetY();
                $this->MultiCell($index == 0 ? $width_td / 2 : $width_td, 8, $header, 0, 'L', true);
                $this->SetXY($x, $y);
            }

            $y = $this->GetY() + $step;
            $this->SetXY($startX, $y);
            $this->SetFillColor(242, 243, 244);
            foreach ($order_products as $order_product) {
                $x = $this->GetX();
                $y = $this->GetY();

                $this->MultiCell($width_td / 2, 6, $order_product->quantity ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td / 2, $y);

                $x = $this->GetX();
                $this->SetFont('helvetica', '', 9);
                $this->MultiCell($width_td, 6, $order_product->description ?? '-', 0, 'L', true);
                $this->SetXY($x + $width_td, $y);

                // Precio unitario y total del producto
                switch ($order_product->approved_supplier_id) {
                    case $order_product->supplier1_id:
                        $total_product = $order_product->supplier1_cost * $order_product->quantity;
                        $total_order += $total_product;

                        $x = $this->GetX();
                        $this->SetFont('helvetica', '', 5);
                        $this->MultiCell($width_td, 6, $order_product->approvedSupplier->name ?? '-', 0, 'L', true);
                        $this->SetXY($x + $width_td, $y);

                        $x = $this->GetX();
                        $this->SetFont('helvetica', '', 9);
                        $this->MultiCell($width_td, 6, '$ ' . $order_product->supplier1_cost ?? '-', 0, 'L', true);
                        $this->SetXY($x + $width_td, $y);

                        $x = $this->GetX();
                        $this->MultiCell($width_td, 6, '$ ' . $total_product ?? '-', 0, 'L', true);
                        break;

                    case $order_product->supplier2_id:
                        $total_product = $order_product->supplier2_cost * $order_product->quantity;
                        $total_order += $total_product;

                        $x = $this->GetX();
                        $this->SetFont('helvetica', '', 5);
                        $this->MultiCell($width_td, 6, $order_product->approvedSupplier->name ?? '-', 0, 'L', true);
                        $this->SetXY($x + $width_td, $y);

                        $x = $this->GetX();
                        $this->SetFont('helvetica', '', 9);
                        $this->MultiCell($width_td, 6, '$ ' . $order_product->supplier2_cost ?? '-', 0, 'L', true);
                        $this->SetXY($x + $width_td, $y);

                        $x = $this->GetX();
                        $this->MultiCell($width_td, 6, '$ ' . $total_product ?? '-', 0, 'L', true);
                        break;

                    default:
                        $x = $this->GetX();
                        $this->MultiCell($width_td, 6, '-', 0, 'L', true);
                        $this->SetXY($x + $width_td, $y);

                        $x = $this->GetX();
                        $this->MultiCell($width_td, 6, '-', 0, 'L', true);
                        break;
                }
                $this->Ln(2);
            }
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetFont('helveticaB', '', 10);
            $this->MultiCell($width_td * 3.5, 6, 'Total', 0, 'L', true);
            $this->SetXY($x + $width_td * 3.5, $y);
            $this->MultiCell($width_td, 6, '$ ' . $total_order, 0, 'L', true);
            $x = $this->GetX();
            $y = $this->GetY();
            $total_with_iva = $total_order * 1.16;
            $this->MultiCell($width_td * 3.5, 6, 'Total con IVA', 0, 'L', true);
            $this->SetXY($x + $width_td * 3.5, $y);
            $this->MultiCell($width_td, 6, '$ ' . number_format($total_with_iva, 2), 0, 'L', true);

            $this->Ln(2);
        } else {
            $y = $this->GetY();
            $this->SetXY($startX, $y);
            $this->SetFont('helveticaB', '', 8);
            $this->Cell(($width - $this->margin) / 2, 4, 'Sin productos', 0, 0, 'L');
            $this->Ln(6);
        }

        $y = $this->GetY();
        $this->SetXY($startX, $y);
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

    // esta funcion no se usa pero la dejo por si acaso
    private function getNameByKey($key, $data)
    {
        $result = collect($data)->firstWhere('key', $key);
        return $result ? $result['name'] : null; // O puedes retornar un mensaje personalizado
    }

    // esta funcion no se usa pero la dejo por si acaso
    private function getSignature($signature_base64)
    {
        $signature = base64_decode($signature_base64);
        $temp_file = tempnam(sys_get_temp_dir(), 'signature_') . '.png';
        file_put_contents($temp_file, $signature);
        return $temp_file;
    }
}
