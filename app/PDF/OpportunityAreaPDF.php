<?php
namespace App\PDF;

use App\Models\ApplicationArea;
use App\Models\ApplicationMethod;
use App\Models\ControlPoint;
use App\Models\DevicePest;
use App\Models\DeviceStates;
use App\Models\OpportunityArea;
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
use App\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class OpportunityAreaPDF extends TCPDF
{
    private $margin, $customer_id, $widthPage, $heightPage;

    public function __construct($customerId)
    {
        parent::__construct();
        $this->margin = 10;
        $this->customer_id = $customerId;
        $this->widthPage = $this->getPageWidth() - $this->margin;
        $this->heightPage = $this->getPageHeight() - $this->margin;
        $this->SetMargins($this->margin, $this->margin * 2, $this->margin);
        //$this->SetAutoPageBreak(true, $this->margin);
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

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);
        $this->watermark();
    }

    public function Header()
    {
        $step = 20;
        $x = $this->GetX();
        $y = $this->GetY() + $step;

        $this->SetFont('helveticaB', '', 18);
        $this->SetTextColor(190, 205, 97);
        $this->Cell($x, $y, 'Reporte de areas de oportunidad', 0, 1, 'L');

        $imgX = ($this->widthPage / 2) + ($this->margin * 2);
        $img_width = 80;
        $img_height = 25;
        $imagePath = public_path('images/logo.png');
        $this->Image($imagePath, $imgX, 0, $img_width, $img_height, 'PNG');
        $this->setXY($x, $y + $img_height);
    }

    public function Customer()
    {
        $step = 20;
        $customer = Customer::find($this->customer_id);

        if ($customer) {
            $this->SetTextColor(0, 0, 0);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetFont('helveticaB', '', 10);
            $this->Cell($x, 1, 'Fecha: ' . date('d-m-Y', strtotime(now())), 0, 1, 'L');

            $this->SetFontSize(size: 8);
            $this->SetFont('helvetica');

            $middleX = ($this->widthPage / 2) + ($this->margin * 2);
            $y = $this->GetY();

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

            $this->Ln(4);
        }
    }

    public function Objective(string $input_objective)
    {
        $step = 20;
        $customer = Customer::find($this->customer_id);

        if ($customer) {
            $x = $this->GetX();
            $y = $this->GetY();
            $width = $this->widthPage - $this->margin;
            $this->SetFont('helveticaB', '', 10);
            $this->SetFillColor(118, 172, 220);
            $this->SetXY($x, y: $y);
            $this->Cell($width, 5, 'OBJETIVO', 0, 0, 'L', true);
            $this->Ln();
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell($width, 4,  $input_objective ?? 'Se realiza recorrido en planta, para detectar áreas de mejora, se describen a continuación, riesgo y recomendación por parte de Siscoplagas, y disminuir la posibilidad de ingreso de organismos a planta.', 0, 'L');
            $this->Ln(4);
        }
    }

    public function Incidents($opAreaIds)
    {
        $step = 10;
        $op_areas = OpportunityArea::where('customer_id', $this->customer_id)->whereIn('id', $opAreaIds)->get();
        $disk = Storage::disk('public');
        $img_max_height = 0;

        if ($op_areas) {
            $x = $this->GetX();
            $y = $this->GetY();
            $width = $this->widthPage - $this->margin;
            $this->SetFont('helveticaB', '', 10);
            $this->SetFillColor(118, 172, 220);
            $this->SetXY($x, y: $y);
            $this->Cell($width, 5, 'INCIDENCIAS', 0, 0, 'L', true);
            $this->Ln(8);

            foreach ($op_areas as $index => $op_area) {
                $newY = $this->GetY();
                $this->SetY($newY);
                if ($newY + $step > $this->heightPage - $this->margin) {
                    $this->AddPage();
                    $newY = $this->GetY();
                }   
                $this->SetFillColor(130, 178, 221);
                $this->Rect($x, $newY, 5, 5, 'F');

                $newX = $x + $step;
                $this->setX($newX);
                $this->setTextColor(40, 116, 166);
                $this->SetFont('helveticaB', '', 10);
                $this->Cell($width, 4, 'INCIDENCIA ' . ($index + 1), 0, 0, 'L');

                $this->SetFont('helvetica', '', 9);
                $this->setTextColor(0, 0, 0);
                $this->Ln(6);
                $this->Cell($width, 4, 'Fecha: ' . date('d-m-Y', strtotime($op_area->date)), 0, 0, 'L');
                $this->Ln();
                $this->Cell($width, 4, 'Responsable: ' . $op_area->customer->name, 0, 0, 'L');
                $this->Ln();
                $this->Cell($width, 4, 'Área: ' . $op_area->applicationArea->name, 0, 0, 'L');
                $this->Ln();
                $this->Cell($width, 4, 'Status: ' . $op_area->getStatus(), 0, 0, 'L');
                $this->Ln();
                $this->Cell($width, 4, 'Fecha estimada: ' . date('d-m-Y', strtotime($op_area->estimated_date)), 0, 0, 'L');
                $this->Ln();
                $this->Cell($width, 4, 'Seguimiento: ' . $op_area->getTracing(), 0, 0, 'L');
                $this->Ln(6);
                $this->MultiCell($width, 4, 'Área de oportunidad: ' . $op_area->opportunity, 0, 'L');
                $this->Ln(2);
                $this->MultiCell($width, 4, 'Recomendación: ' . $op_area->recommendation, 0, 'L');
                $this->Ln(4);
                $this->SetFont('helveticaB', '', 10);
                $middleX = ($this->widthPage / 2);
                $middle_col_width = ($this->widthPage - ($this->margin * 2)) / 2; // Divide la página en 2 columnas                
                $imgY = $this->GetY() + $step;

                if ($op_area->img_incidence) {
                    $imagePath = $disk->path($op_area->img_incidence);
                    list($origin_img_width, $origin_img_height) = getimagesize($imagePath);
                    $img_width = $middle_col_width / 1.1;
                    $img_height = ($origin_img_height * $img_width) / $origin_img_width;

                    if ($imgY + $img_height > $this->heightPage - $this->margin) {
                        $this->AddPage();
                        $imgY = $this->getY() + $step;
                    }

                    $this->Cell($width, 4, 'Imagen de incidencia', 0, 0, 'L');
                    $this->Image($imagePath, $x, $imgY, $img_width, $img_height);

                    if ($img_max_height < $img_height) {
                        $img_max_height = $img_height;
                    }
                }

                if ($op_area->img_conclusion) {
                    $imagePath = $disk->path($op_area->img_conclusion);
                    list($origin_img_width, $origin_img_height) = getimagesize($imagePath);
                    $img_width = $middle_col_width / 1.1;
                    $img_height = ($origin_img_height * $img_width) / $origin_img_width;

                    if ($imgY + $img_height > $this->heightPage - $this->margin) {
                        $this->AddPage();
                        $imgY = $this->getY() + $step;
                    }
                    $this->SetX($middleX);
                    $this->Cell($width, 4, 'Evidencia de la conclusión', 0, 0, 'L');
                    $this->Image($imagePath, $middleX, $imgY, $img_width, $img_height);

                    if ($img_max_height < $img_height) {
                        $img_max_height = $img_height;
                    }
                }

                $this->setXY(
                    $x,
                    $imgY + $img_max_height + $step / 2
                );
            }
        }
    }
}