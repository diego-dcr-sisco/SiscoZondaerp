<?php
namespace App\PDF;

use App\Models\OrderProduct;
use App\Models\LineBusiness;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\FloorPlans;
use App\Models\ControlPoint;
use App\Models\Device;

use TCPDF;

Class PDF extends TCPDF{
    private $widthPage;
    private $fplan_id;

    public function SetDatos($id, $widthPage){
        $Fplan = FloorPlans::find($id);

        if($Fplan){
            $this->widthPage = $widthPage;
            $this->fplan_id = $id;
        }else {
            throw new \Exception("Order not found.");
        }
    }
    public function Header(){
        $Fplan = FloorPlans::find($this->fplan_id);
        if($Fplan){
            $customer = Customer::find($Fplan->customer_id);
            $branch = Branch::find($customer->branch_id);
            $this->SetFontSize(14);
            $this->SetTextColor(190, 205, 97);
            $this->Cell(0, 20,  $Fplan->filename, 0, 1, 'L');
            
            $ancI = 80;
            $altI = 25;
            $margin = 10;
            // Ruta de la imagen
            $imagePath = public_path('images/logo.png');
            // (ruta de imagen, codx, cordy, ancho, alto, formato)
            $this->Image($imagePath, $this->widthPage + $margin - $ancI, 0, $ancI, $altI, 'PNG');
            $this->SetFontSize(8);
            $this->SetTextColor(0, 0, 0);
            $y = $this->GetY();
            $x = $margin;
                
            if (!empty($serv_lines)) {
                foreach ($serv_lines as $item) {
                    $this->SetFontSize(10);
                    $this->Cell(0, 1, $item, 0, 1, 'L');
                }
            }
                
         // Definir un nuevo margen adicional para desplazar hacia la derecha
        $additional_margin = 50; // Ajusta este valor según tus necesidades

        $this->SetXY(($this->widthPage / 2) + ($margin * 3) + $additional_margin, $y);
        $this->Cell(0, 1, $branch->fiscal_name, 0, 1, 'C');
        $this->SetXY(($this->widthPage / 2) + ($margin * 3) + $additional_margin, $this->GetY());
        $this->Cell(0, 1, $branch->address, 0, 1, 'C');
        $this->SetXY(($this->widthPage / 2) + ($margin * 3) + $additional_margin, $this->GetY());
        $this->Cell(0, 1, $branch->colony . ' #' . $branch->zip_code . ', ' . $branch->state . ', ' . $branch->country, 0, 1, 'C');
        $this->SetXY(($this->widthPage / 2) + ($margin * 3) + $additional_margin, $this->GetY());
        $this->Cell(0, 1, $branch->email, 0, 1, 'C');
                        
        $this->SetFillColor(255, 255, 255);
        $cont = 'Licencia Sanitaria nº : ' . $branch->license_number;
        $nameTel = $branch->name . ' Tel. ' . $branch->phone . " ";
                        
        $this->SetXY(($this->widthPage / 2) + ($margin * 3) + $additional_margin, $this->GetY());
        $this->MultiCell(0, 1, $cont . " " . $nameTel . " ", 0, 'C');

        }
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetAutoPageBreak(true, 15);
        $this->SetFontSize(8);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 1, 'SISCOPLAGAS', 0, false, 'C');
        $this->Cell(0, 1, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'R');
    }

    public function agregarContenido() {
        $this->SetY(45); 
    }
}