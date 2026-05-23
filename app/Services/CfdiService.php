<?php

namespace App\Services;

use App\Models\Invoice;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Nodes\XmlNodeUtils;
use PhpCfdi\Credentials\Credential;
use CfdiUtils\CertificateLoader;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CfdiService
{
    protected $certificatePath;
    protected $keyPath;
    protected $password;

    public function __construct()
    {
        $this->certificatePath = Storage::path('invoices/certificates/certificate.cer');
        $this->keyPath = Storage::path('invoices/certificates/private_key_new.key');
        $this->password = config('services.sat.password');

        // Verificar que los archivos existan
        if (!file_exists($this->certificatePath)) {
            throw new \RuntimeException('El archivo del certificado no existe en: ' . $this->certificatePath);
        }

        if (!file_exists($this->keyPath)) {
            throw new \RuntimeException('El archivo de la llave privada no existe en: ' . $this->keyPath);
        }

        // Verificar formato PEM
        $privateKey = file_get_contents($this->keyPath);
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
            throw new \RuntimeException('La llave privada no está en formato PEM válido');
        }
    }

    protected function loadCredentials()
    {
        try {
            $certificate = file_get_contents($this->certificatePath);
            $privateKey = file_get_contents($this->keyPath);

            // Verificar que el contenido sea válido
            if (empty($privateKey)) {
                throw new \RuntimeException('El archivo de llave privada está vacío');
            }

            $credential = Credential::create(
                $certificate,
                $privateKey,
                $this->password
            );

            return $credential;
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al cargar las credenciales: ' . $e->getMessage());
        }
    }

    public function generateXml(Invoice $invoice)
    {
        try {
            // Cargar credenciales primero
            $credential = $this->loadCredentials();
            $privateKey = file_get_contents($this->keyPath);
            
            // Extraer solo el número del folio (sin prefijo)
            $folioNumber = str_replace(['FAC-', 'FAC'], '', $invoice->folio);
            
            $creator = new CfdiCreator40([
                'Version' => '4.0',
                'Serie' => $invoice->serie,
                'Folio' => $folioNumber, // Solo el número
                'Fecha' => (is_object($invoice->issue_date) ? $invoice->issue_date : new \DateTime($invoice->issue_date))->format('Y-m-d\TH:i:s'),
                // 'FormaPago' => $invoice->payment_type,
                'MetodoPago' => $invoice->payment_method == 1 ? 'PPD' : 'PUE',
                'TipoDeComprobante' => 'I',
                'LugarExpedicion' => config('services.sat.zip_code'), // Solo código postal
                'Moneda' => $invoice->currency,
                'SubTotal' => number_format($invoice->subtotal, 2, '.', ''),
                'Total' => number_format($invoice->total, 2, '.', ''),
                'Exportacion' => '01', // Campo obligatorio en 4.0
            ]);

            $comprobante = $creator->comprobante();

            // Emisor - Solo campos permitidos en CFDI 4.0
            $comprobante->addEmisor([
                'Rfc' => config('services.sat.rfc'),
                'Nombre' => config('services.sat.business_name'),
                'RegimenFiscal' => config('services.sat.tax_regime'), // Debe ser código como "601"
            ]);

            // Receptor
            $comprobante->addReceptor([
                'Rfc' => $invoice->customer->rfc,
                'Nombre' => $invoice->customer->name,
                'UsoCFDI' => explode('-', $invoice->cfdi_use)[0],
                'DomicilioFiscalReceptor' => $invoice->customer->zip_code,
                'RegimenFiscalReceptor' => $invoice->customer->taxRegime->code ?? '601', // Debe ser código
            ]);

            // Conceptos
            $conceptos = $comprobante->addConceptos();
            $totalImpuestos = 0;
            
            foreach ($invoice->items as $item) {
                $impuestoItem = $item->total * 0.16;
                $totalImpuestos += $impuestoItem;
                
                $concepto = $conceptos->addConcepto([
                    'ClaveProdServ' => $item->item_code ?: '80161500', // Código por defecto para servicios de control de plagas
                    'Cantidad' => number_format($item->quantity, 2, '.', ''),
                    'ClaveUnidad' => $item->item_code ? 'ACT' : 'ACT', 
                    'Unidad' => 'Actividad',
                    'Descripcion' => $item->description,
                    'ValorUnitario' => number_format($item->price, 2, '.', ''),
                    'Importe' => number_format($item->total, 2, '.', ''),
                    'ObjetoImp' => '02',
                ]);

                // Agregar impuestos al concepto
                $conceptoImpuestos = $concepto->addImpuestos();
                $traslados = $conceptoImpuestos->addTraslados();
                $traslados->addTraslado([
                    'Base' => number_format($item->total, 2, '.', ''),
                    'Impuesto' => '002',
                    'TipoFactor' => 'Tasa',
                    'TasaOCuota' => '0.160000',
                    'Importe' => number_format($impuestoItem, 2, '.', ''),
                ]);
            }

            // Agregar impuestos al comprobante
            $impuestos = $comprobante->addImpuestos([
                'TotalImpuestosTrasladados' => number_format($totalImpuestos, 2, '.', '')
            ]);
            
            $traslados = $impuestos->addTraslados();
            $traslados->addTraslado([
                'Base' => number_format($invoice->subtotal, 2, '.', ''),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe' => number_format($totalImpuestos, 2, '.', ''),
            ]);

            // Calcular sumas y agregar sello
            $creator->addSumasConceptos(null, 2);
            $creator->addSello($privateKey, $this->password);

            $xmlPath = storage_path('app/invoices/xml/' . $invoice->folio . '.xml');
            file_put_contents($xmlPath, $creator->asXml());

            $invoice->update([
                'xml_file' => $xmlPath,
                'status' => 6, // En proceso
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error generando XML: ' . $e->getMessage());
            throw $e;
        }
    }
}
