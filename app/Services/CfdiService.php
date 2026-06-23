<?php

namespace App\Services;

use App\Models\Invoice;
use CfdiUtils\CfdiCreator40;
use PhpCfdi\Credentials\Credential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CfdiService
{
    protected $certificatePath;
    protected $keyPath;
    protected $password;

    public function __construct()
    {
        // Rutas definidas pero NO leídas en el constructor para evitar bloqueos
        $this->certificatePath = storage_path('app/invoices/certificates/certificate.cer');
        $this->keyPath = storage_path('app/invoices/certificates/private_key_new.key');
        $this->password = config('services.sat.password');
    }

    protected function loadCredentials()
    {
        // Verificación de seguridad antes de intentar cargar archivos
        if (!file_exists($this->certificatePath) || !file_exists($this->keyPath)) {
            Log::error('Certificados no encontrados para CFDI');
            throw new \RuntimeException('No se encontraron los archivos de certificado para facturar.');
        }

        try {
            $certificate = file_get_contents($this->certificatePath);
            $privateKey = file_get_contents($this->keyPath);

            return Credential::create($certificate, $privateKey, $this->password);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al cargar las credenciales: ' . $e->getMessage());
        }
    }

    public function generateXml(Invoice $invoice)
    {
        // Esta lógica solo se ejecutará cuando intentes facturar, 
        // no al cargar el sistema, evitando errores en el reporte.
        try {
            $credential = $this->loadCredentials();
            $privateKey = file_get_contents($this->keyPath);
            
            $folioNumber = str_replace(['FAC-', 'FAC'], '', $invoice->folio);
            
            $creator = new CfdiCreator40([
                'Version' => '4.0',
                'Serie' => $invoice->serie,
                'Folio' => $folioNumber,
                'Fecha' => (is_object($invoice->issue_date) ? $invoice->issue_date : new \DateTime($invoice->issue_date))->format('Y-m-d\TH:i:s'),
                'MetodoPago' => $invoice->payment_method == 1 ? 'PPD' : 'PUE',
                'TipoDeComprobante' => 'I',
                'LugarExpedicion' => config('services.sat.zip_code', '00000'),
                'Moneda' => $invoice->currency,
                'SubTotal' => number_format($invoice->subtotal, 2, '.', ''),
                'Total' => number_format($invoice->total, 2, '.', ''),
                'Exportacion' => '01',
            ]);

            $comprobante = $creator->comprobante();
            $comprobante->addEmisor([
                'Rfc' => config('services.sat.rfc', 'AAA010101AAA'),
                'Nombre' => config('services.sat.business_name', 'Empresa'),
                'RegimenFiscal' => config('services.sat.tax_regime', '601'),
            ]);

            $comprobante->addReceptor([
                'Rfc' => $invoice->customer->rfc,
                'Nombre' => $invoice->customer->name,
                'UsoCFDI' => explode('-', $invoice->cfdi_use)[0] ?? 'P01',
                'DomicilioFiscalReceptor' => $invoice->customer->zip_code,
                'RegimenFiscalReceptor' => $invoice->customer->taxRegime->code ?? '601',
            ]);

            $conceptos = $comprobante->addConceptos();
            foreach ($invoice->items as $item) {
                $conceptos->addConcepto([
                    'ClaveProdServ' => $item->item_code ?: '80161500',
                    'Cantidad' => number_format($item->quantity, 2, '.', ''),
                    'ClaveUnidad' => 'ACT',
                    'Descripcion' => $item->description,
                    'ValorUnitario' => number_format($item->price, 2, '.', ''),
                    'Importe' => number_format($item->total, 2, '.', ''),
                    'ObjetoImp' => '02',
                ]);
            }

            $creator->addSumasConceptos(null, 2);
            $creator->addSello($privateKey, $this->password);

            $xmlPath = storage_path('app/invoices/xml/' . $invoice->folio . '.xml');
            file_put_contents($xmlPath, $creator->asXml());

            return true;

        } catch (\Exception $e) {
            Log::error('Error generando XML: ' . $e->getMessage());
            throw $e;
        }
    }
}