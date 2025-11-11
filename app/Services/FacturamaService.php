<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Payroll;
use Illuminate\Support\Facades\Storage;

use \Facturama\Client as FacturamaClient;

class FacturamaService
{
    protected $user;
    protected $password;
    protected $facturama_client;

    public function __construct($_user, $_password)
    {
        $this->user = $_user;
        $this->password = $_password;
        $this->facturama_client = new FacturamaClient($_user, $_password);
    }

    public function createCustomer()
    {
        $params = [
            'Address' => [
                'Street' => 'St One ',
                'ExteriorNumber' => '15',
                'InteriorNumber' => '12',
                'Neighborhood' => 'Lower Manhattan, ',
                'ZipCode' => 'sample string 5',
                'Locality' => 'sample string 6',
                'Municipality' => 'sample string 7',
                'State' => 'sample string 8',
                'Country' => 'MX',
            ],
            "Rfc" => "XAMA620210DQ5",
            "Name" => "ALBA XKARAJAM MENDEZ",
            "CfdiUse" => "G03",
            "TaxZipCode" => "83410",
            "FiscalRegime" => "626",
            "Email" => "test@facturama.com"
        ];

        $cliente = $this->facturama_client->post('Client', $params);
        return $cliente;
    }

    private function generateJSONInvoice(string $id)
    {
        $sat_config = config('services.sat');
        $invoice = Invoice::find($id);
        $items = [];

        foreach ($invoice->items as $item) {
            // Las facturas CFDIs 4.0 ya no incluyen el campo del descuento por lo que se debe descontar del precio unitario y del subtotal.
            $items[] = [
                "Quantity" => $item->quantity,
                "ProductCode" => $item->product_code,
                "UnitCode" => $item->unit_code,
                "Unit" => $item->unit,
                "Description" => $item->description ?? '',
                "IdentificationNumber" => $item->identification_number, //Opcional, para especificar el número de serie de un producto 
                "UnitPrice" => $item->unit_price - ($item->unit_price * $item->discount_rate),
                "Subtotal" => $item->subtotal,
                "TaxObject" => $item->tax_object,
                //
                // 01 - No objeto de impuesto
                // 02 - (Sí objeto de impuesto), se deben desglosar los Impuestos a nivel de Concepto.
                // 03 - (Sí objeto del impuesto y no obligado al desglose) no se desglosan impuestos a nivel Concepto.
                // 04 - (Sí Objeto de impuesto y no causa impuesto)
                "Taxes" => [
                    [
                        "Name" => "IVA",
                        "Rate" => $item->tax_rate,
                        "Total" => $item->tax_total,
                        "Base" => $item->tax_base,
                        "IsRetention" => false, // CAMBIO: boolean real
                        "IsFederalTax" => true  // CAMBIO: boolean real
                    ]
                ],
                "Total" => (float) $item->total,
            ];
        }

        $invoiceData = [
            'CfdiType' => 'I',
            "NameId" => '1', // CAMBIO: Debe ser número (como string o entero)
            "ExpeditionPlace" => $sat_config['zip_code'],
            //"Serie" => null,
            "Folio" => $invoice->folio,
            "PaymentForm" => $invoice->payment_form,
            "PaymentMethod" => $invoice->payment_method,
            "Exportation" => "01",
            // Campo de exporatacion
            // 01 - No aplica
            // 02 - Definitiva con clave A1
            // 03 - Temporal
            // 04 - Definitiva con clave distinta a A1 o cuando no existe enajenación en términos del CFF

            // #------------------- DONT CHANGE -------------------#
            "Date" => date('Y-m-d\TH:i:s', time()),
            'Issuer' => [
                'Rfc' => $sat_config['rfc'], // El RFC de tu empresa
                'Name' => $sat_config['business_name'], // Exactamente como en el SAT
                'FiscalRegime' => $sat_config['tax_regime'] // Régimen fiscal de tu empresa
            ],

            // #--------------------------------------------------#
            "Receiver" => [
                "Name" => $invoice->receiver_name ?? $invoice->customer->social_reason,
                "Rfc" => $invoice->receiver_rfc ?? $invoice->customer->rfc,
                "CfdiUse" => $invoice->cfdi_usage ?? $invoice->customer->cfdi_usage,
                "FiscalRegime" => $invoice->receiver_fiscal_regime ?? $invoice->customer->tax_system,
                "TaxZipCode" => $invoice->receiver_zip_code ?? $invoice->customer->zip_code
            ],

            'Items' => $items,
        ];

        //dd($invoiceData);
        return $invoiceData;
    }

    private function generateJSONCreditNote(string $id)
    {
        $sat_config = config('services.sat');
        $items = [];

        $credit_note = CreditNote::find($id);

        foreach ($credit_note->items as $item) {
            $items[] = [
                'Quantity' => $item->quantity,
                'ProductCode' => $item->product_code,
                'UnitCode' => $item->unit_code,
                'Unit' => $item->unit,
                'Description' => $item->description,
                'UnitPrice' => $item->unit_price - ($item->unit_price * $item->discount_rate),
                'Subtotal' => $item->subtotal,
                'TaxObject' => $item->tax_object,
                'Taxes' => [
                    [
                        'Name' => 'IVA',
                        'Rate' => $item->tax_rate,
                        'Total' => $item->tax_total,
                        'Base' => $item->tax_base,
                        'IsRetention' => false,
                        'IsFederalTax' => true
                    ]
                ],
                'Total' => $item->total,
            ];
        }

        $invoiceData = [
            'CfdiType' => 'E',
            'NameId' => '2', // CAMBIO: Debe ser número (como string o entero)
            'ExpeditionPlace' => $sat_config['zip_code'],
            'PaymentForm' => $credit_note->payment_form,
            'PaymentMethod' => $credit_note->payment_method,
            'Receiver' => [
                'Name' => $credit_note->receiver_name,
                'Rfc' => $credit_note->receiver_rfc,
                'CfdiUse' => $credit_note->receiver_cfdi_use,
                'FiscalRegime' => $credit_note->receiver_fiscal_regime,
                'TaxZipCode' => $credit_note->receiver_tax_zip_code
            ],
            'Relations' => [
                'Type' => '01',
                'Cfdis' => [
                    [
                        'Uuid' => $credit_note->cfdi_uuid,
                    ]
                ],
            ],
            'Items' => $items,
        ];
        return $invoiceData;
    }

    public function generateJSONPayment(string $id)
    {
        $payment = Payment::find($id);
        $items = [];
        $docs = [];

        foreach ($payment->items as $item) {
            foreach ($item->relatedDocuments as $doc) {
                $docs[] = [
                    "TaxObject" => $doc->tax_object,
                    "Uuid" => $doc->cfdi_uuid,
                    "Serie" => $doc->serie,
                    "Folio" => $doc->folio,
                    "Currency" => $item->currency,
                    "PaymentMethod" => $doc->payment_method,
                    "PartialityNumber" => $doc->partiality_number,
                    "PreviousBalanceAmount" => $doc->previous_balance_amount,
                    "AmountPaid" => $doc->amount_paid,
                    "ImpSaldoInsoluto" => $doc->imp_saldo_insoluto,
                    "Taxes" => [
                        [
                            "Total" => $doc->tax_total,
                            "Rate" => $doc->tax_rate,
                            "Name" => $doc->tax_name,
                            "Base" => $doc->tax_base,
                            "IsRetention" => false
                        ]
                    ]
                ];
            }

            $items[] = [
                "Date" => $item->payment_date,
                "PaymentForm" => $item->payment_form,
                "Amount" => $item->amount,
                "Currency" => $item->currency,
                "RelatedDocuments" => $docs,
            ];
        }

        $paymentData = [
            "CfdiType" => $payment->cfdiType ?? "P",
            "NameId" => "14",
            "Folio" => $payment->folio,
            "ExpeditionPlace" => $payment->expedition_place,
            "Receiver" => [
                "Rfc" => $payment->receiver_rfc,
                "CfdiUse" => $payment->receiver_cfdi_use ?? "CP01",
                "Name" => $payment->receiver_name,
                "FiscalRegime" => $payment->receiver_fiscal_regime,
                "TaxZipCode" => $payment->receiver_tax_zip_code
            ],
            "Complemento" => [
                "Payments" => $items
            ]
        ];

        //dd($paymentData);
        return $paymentData;
    }

    public function generateJSONPayroll(string $id)
    {
        $payroll = Payroll::find($id);
        $perceptions = [];
        $deductions = [];
        $otherPayments = [];

        $items = [];

        // Implementación pendiente de generación de nómina en formato JSON para Facturama

        foreach ($payroll->perceptions as $perception) {
            $perceptions[] = [
                "PerceptionType" => $perception->perception_type,
                "Code" => $perception->code,
                "Description" => $perception->description,
                "TaxedAmount" => $perception->taxed_amount,
                "ExemptAmount" => $perception->exempt_amount
            ];
        }

        foreach ($payroll->deductions as $deduction) {
            $deductions[] = [
                "DeduccionType" => $deduction->deduction_type,
                "Code" => $deduction->code,
                "Description" => $deduction->description,
                "Amount" => $deduction->amount
            ];
        }

        $payrollData = [
            "NameId" => 16,
            "ExpeditionPlace" => $payroll->expedition_place,
            "Folio" => $payroll->folio,
            "CfdiType" => "N",
            "PaymentMethod" => $payroll->payment_method,

            "Receiver" => [
                "Rfc" => $payroll->employee_rfc,
                "CfdiUse" => $payroll->cfdi_use,
                "Name" => $payroll->employee_name,
                "FiscalRegime" => $payroll->tax_regime,
                "TaxZipCode" => $payroll->employee_zip_code
            ],
                                                        
            "Complemento" => [
                "Payroll" => [
                    "Type" => "O",
                    "DailySalary" => 0.00,
                    "BaseSalary" => 0.00,
                    "PaymentDate" => $payroll->payment_date,
                    "InitialPaymentDate" => $payroll->initial_payment_date,
                    "FinalPaymentDate" => $payroll->final_payment_date,
                    "DaysPaid" => $payroll->days_paid,
                    "Issuer" => [
                        "EmployerRegistration" => "B5510768108"
                    ],
                    "Employee" => [
                        "Curp" => $payroll->employee_curp,
                        "SocialSecurityNumber" => $payroll->employee_nss,
                        "PositionRisk" => $payroll->position_risk,
                        "ContractType" => '01', //$payroll->contract_type,
                        "RegimeType" => '02', //$payroll->tax_regime,
                        "Unionized" => false,
                        "TypeOfJourney" => "01",
                        "EmployeeNumber" => $payroll->employee_number ?? '001',
                        "Department" => 'Sistemas',
                        "Position" => 'Dev',
                        "FrequencyPayment" => $payroll->frequency_payment,
                        "FederalEntityKey" => "SLP",
                        "DailySalary" => (float)$payroll->employee_daily_salary,
                        "StartDateLaborRelations" => $payroll->start_date_labor_relations ?? '2025-01-01'
                    ],
                    "Perceptions" => [
                        "Details" => [
                            [
                                "PerceptionType" => "001",
                                "Code" => "001",
                                "Description" => "Sueldos, Salarios  Rayas y Jornales",
                                "TaxedAmount" => 10.00,
                                "ExemptAmount" => 0.00
                            ]
                        ]
                    ],
                    "Deductions" => [
                        "Details" => [
                            [
                                "DeduccionType" => "002",
                                "Code" => "002",
                                "Description" => "ISR",
                                "Amount" => 3.00
                            ]
                        ]
                    ],
                    "OtherPayments" => [
                        [
                            "OtherPaymentType" => "002", // OBLIGATORIO
                            "Code" => "002",
                            "Description" => "Subsidio para el empleo",
                            "Amount" => 0.00, // Cero si no aplica
                            "EmploymentSubsidy" => [
                                "Amount" => 0.00 // Cero si no aplica
                            ]
                        ]
                    ]
                ]
            ]
        ];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      

        dd(json_encode($payrollData, JSON_PRETTY_PRINT));

        return $payrollData;
    }

    public function createInvoice(string $id)
    {
        try {
            $invoiceJson = $this->generateJSONInvoice($id);
            //dd(json_encode($invoiceJson));
            $response = $this->facturama_client->post('/3/cfdis', $invoiceJson);
            return [
                'success' => true,
                'data' => $response,
                'message' => 'Factura creada exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage() . ' Factura: ' . $e->getPrevious()->getMessage()
            ];
        }
    }

    public function createCreditNote(string $id)
    {
        try {
            $invoiceJson = $this->generateJSONCreditNote($id);
            $response = $this->facturama_client->post('/3/cfdis', $invoiceJson);
            return [
                'success' => true,
                'data' => $response,
                'message' => 'Nota de credito creada exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage() . ' Factura: ' . $e->getPrevious()->getMessage()
            ];
        }
    }

    public function createPayment(string $id)
    {
        try {
            $invoiceJson = $this->generateJSONPayment($id);
            $response = $this->facturama_client->post('/3/cfdis', $invoiceJson);
            return [
                'success' => true,
                'data' => $response,
                'message' => 'Complemento de pago creado exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage() . ' Complemento de pago: ' . $e->getPrevious()->getMessage()
            ];
        }
    }

    public function createPayroll(string $id)
    {
        try {
            $payrollJson = $this->generateJSONPayroll($id);
            //dd(json_encode($payrollJson, JSON_PRETTY_PRINT));
            $response = $this->facturama_client->post('/3/cfdis', $payrollJson);
            return [
                'success' => true,
                'data' => $response,
                'message' => 'Nomina Timbrada Exitosamente'
            ];
        } catch (\Exception $e) {
            dd($e);
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error: ' . $e->getMessage() . ' Complemento de pago: ' . $e->getPrevious()->getMessage()
            ];
        }
    }

    public function getInvoices(
        string $type,
        string $rfc,
        string $taxEntityName,
        string $status,
        string $orderNumber,
        $folio_range = null,
        $date_range = null,
        $page
    ) {
        $dateStart = null; // dia/mes/año - dd/mm/aaaa
        $dateEnd = null;

        $folioStart = null;
        $folioEnd = null;

        if ($folio_range) {
            $dateStart = $folio_range[0] ?? null;
            $dateEnd = $folio_range[1] ?? null;
        }

        if ($date_range) {
            $folioStart = $date_range[0] ?? null;
            $folioEnd = $date_range[1] ?? null;
        }

        $params = [
            'type' => $type, // issued -> Emitidas
            // received -> Recibidas
            // payroll -> Nomina
            'rfc' => $rfc,
            'taxEntityName' => $taxEntityName,
            'status' => $status,
            'orderNumber' => $orderNumber,  // true -> Muestra únicamente las que SI tienen Número de Orden
            // false -> Muestra únicamente las que NO tienen Número de Orden
            // ValorEspecífico Muestra las que tienen específicamente el número de orden. Ejemplo de valor específico: P-E-005036-1-1

            'folioStart' => $folioStart,
            'folioEnd' => $folioEnd,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'page' => $page,    // page=0 -> Representa los primeros 100 elementos (del 1 al 100)
            // page=1 -> Representa los segundos 100 elementos (del 101 al 200) etc.
        ];

        $invoices = $this->facturama_client->get('/cfdi', $params);
        return $invoices;
    }


    public function getInvoiceFormat(string $_format, string $_type, string $_id)
    {
        $params = [];

        // Obtener el archivo de Facturama
        $result = $this->facturama_client->get('cfdi/' . $_format . '/' . $_type . '/' . $_id, $params);

        // Decodificar el contenido base64
        $fileContent = base64_decode(end($result));

        // Generar nombre del archivo
        $filename = 'factura_' . $_id . '.' . $_format;

        // Guardar usando Storage
        $path = '/stampedXML/' . $filename;

        $saved = Storage::disk('invoice')->put($path, $fileContent);

        if ($saved) {
            // Opcional: Retornar información del archivo guardado
            return [
                'success' => true,
                'message' => 'Archivo guardado exitosamente',
                'path' => $path,
                'full_path' => Storage::disk('invoice')->path($path)
            ];
        } else {
            throw new \Exception('Error al guardar el archivo en el storage');
        }
    }
}

//composer require facturama/facturama-php-sdk:^2.0@dev