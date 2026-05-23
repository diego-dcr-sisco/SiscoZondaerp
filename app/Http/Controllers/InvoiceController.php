<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceConcept;
use App\Models\InvoiceCustomer;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\PaymentsRelatedDocument;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// carbon
use Carbon\Carbon;
use App\Services\CfdiService;
use Illuminate\Support\Facades\Storage;
use App\Mail\InvoiceSent;
use Illuminate\Support\Facades\Mail;
use Artisaninweb\SoapWrapper\SoapWrapper;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\Services\FacturamaService as FacturamaService;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

// ---------------------------------------------------
// Facturama Account
//  user: DiegoChaconRivera
//  password: s1sc0.d3vp
//  email: dev.siscoplagas@gmail.com
// ---------------------------------------------------

class InvoiceController extends Controller
{
    protected $cfdiService;

    protected $facturama_user = 'DiegoChaconRivera';

    protected $facturama_password = 's1sc0.d3vp';

    protected $unitCodes;

    protected $navigation;

    protected $taxObjects;

    protected $paymentForms;

    protected $paymentMethods;

    protected $status;

    protected $cfdiTypes;

    protected $cfdiUsages;

    protected $taxRegimes;


    private function generateFolio(int $id, int $longitud = 6): string
    {
        return str_pad($id, $longitud, '0', STR_PAD_LEFT);
    }

    public function __construct(CfdiService $cfdiService)
    {
        $this->cfdiService = $cfdiService;
        $this->navigation = [
            'Dashboard' => route('invoices.dashboard'),
            'Contribuyentes' => route('invoices.customers'),
            'Conceptos' => route('invoices.concepts'),
            'Facturas' => route('invoices.index'),
            'Notas de credito' => route('invoices.credit-notes.index'),
            'Complementos de pago' => route('invoices.payments.index'),
            'Nomina' => route('payrolls.index'),
            'Ordenes de Servicio' => route('order.index'),
            'Contratos' => route('contract.index'),
        ];

        $this->unitCodes = [
            'H87' => 'Pieza',
            'EA' => 'Elemento',
            'E48' => 'Unidad de Servicio',
            'ACT' => 'Actividad',
            'KGM' => 'Kilogramo',
            'E51' => 'Trabajo',
            'A9' => 'Tarifa',
            'MTR' => 'Metro',
            'AB' => 'Paquete a granel',
            'BB' => 'Caja base',
            'KT' => 'Kit',
            'SET' => 'Conjunto',
            'LTR' => 'Litro',
            'XBX' => 'Caja',
            'MON' => 'Mes',
            'HUR' => 'Hora',
            'MTK' => 'Metro cuadrado',
            '11' => 'Equipos',
            'MGM' => 'Miligramo',
            'XPK' => 'Paquete',
            'XKI' => 'Kit (Conjunto de piezas)',
            'AS' => 'Variedad',
            'GRM' => 'Gramo',
            'PR' => 'Par',
            'DPC' => 'Docenas de piezas',
            'xun' => 'Unidad',
            'DAY' => 'Día',
            'XLT' => 'Lote',
            '10' => 'Grupos',
            'MLT' => 'Mililitro',
            'E54' => 'Viaje',
        ];

        $this->taxObjects = [
            '01' => 'No objeto de impuesto',
            '02' => '(Sí objeto de impuesto), se deben desglosar los Impuestos a nivel de Concepto',
            '03' => '(Sí objeto del impuesto y no obligado al desglose) no se desglosan impuestos a nivel Concepto',
            '04' => '(Sí Objeto de impuesto y no causa impuesto)',
        ];

        $this->paymentForms = [
            '01' => 'Efectivo',
            '02' => 'Cheque nominativo',
            '03' => 'Transferencia electrónica de fondos',
            '04' => 'Tarjeta de crédito',
            '05' => 'Monedero electrónico',
            '06' => 'Dinero electrónico',
            '08' => 'Vales de despensa',
            '12' => 'Dación en pago',
            '13' => 'Pago por subrogación',
            '14' => 'Pago por consignación',
            '15' => 'Condonación',
            '17' => 'Compensación',
            '23' => 'Novación',
            '24' => 'Confusión',
            '25' => 'Remisión de deuda',
            '26' => 'Prescripción o caducidad',
            '27' => 'A satisfacción del acreedor',
            '28' => 'Tarjeta de débito',
            '29' => 'Tarjeta de servicios',
            '30' => 'Aplicación de anticipos',
            '31' => 'Intermediarios',
            '99' => 'Por definir'
        ];

        $this->paymentMethods = [
            'PUE' => 'Pago en una sola exhibición',
            'PPD' => 'Pago en parcialidades ó diferido',
        ];

        $this->status = [
            'paid' => 'Pagada',
            '1' => 'Pendiente',
            '2' => 'Vencida',
            '3' => 'Cancelada',
            '4' => 'Vigente',
            '5' => 'Timbrada',
            '6' => 'Parcial',
            '7' => 'En Proceso',
            '8' => 'Enviada',
            '9' => 'Rechazada'
        ];

        $this->cfdiTypes = [
            'I' => 'Ingreso',
            'E' => 'Egreso',
            'T' => 'Traslado',
            'N' => 'Nota de credito',
            'P' => 'Pago'
        ];

        $this->taxRegimes = [
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "General de Ley Personas Morales",
                "Value" => "601"
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Personas Morales con Fines no Lucrativos",
                "Value" => "603"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Sueldos y Salarios e Ingresos Asimilados a Salarios",
                "Value" => "605"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Arrendamiento",
                "Value" => "606"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de EnajenaciregimenesFiscales =ón o Adquisición de Bienes",
                "Value" => "607"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Demás ingresos",
                "Value" => "608"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Residentes en el Extranjero sin Establecimiento Permanente en México",
                "Value" => "610"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Ingresos por Dividendos (socios y accionistas)",
                "Value" => "611"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Personas Físicas con Actividades Empresariales y Profesionales",
                "Value" => "612"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Ingresos por intereses",
                "Value" => "614"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de los ingresos por obtención de premios",
                "Value" => "615"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Sin obligaciones fiscales",
                "Value" => "616"
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Sociedades Cooperativas de Producción que optan por diferir sus ingresos",
                "Value" => "620"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Incorporación Fiscal",
                "Value" => "621"
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras",
                "Value" => "622"
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Opcional para Grupos de Sociedades",
                "Value" => "623"
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Coordinados",
                "Value" => "624"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas",
                "Value" => "625"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Régimen Simplificado de Confianza",
                "Value" => "626"
            ]
        ];


        $this->cfdiUsages = [
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Nómina",
                "Value" => "CN01"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Pagos",
                "Value" => "CP01"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Honorarios médicos, dentales y gastos hospitalarios.",
                "Value" => "D01"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos médicos por incapacidad o discapacidad",
                "Value" => "D02"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos funerales.",
                "Value" => "D03"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Donativos.",
                "Value" => "D04"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).",
                "Value" => "D05"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Aportaciones voluntarias al SAR.",
                "Value" => "D06"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Primas por seguros de gastos médicos.",
                "Value" => "D07"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos de transportación escolar obligatoria.",
                "Value" => "D08"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.",
                "Value" => "D09"
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Pagos por servicios educativos (colegiaturas)",
                "Value" => "D10"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Adquisición de mercancias",
                "Value" => "G01"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Devoluciones, descuentos o bonificaciones",
                "Value" => "G02"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Gastos en general",
                "Value" => "G03"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Construcciones",
                "Value" => "I01"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Mobilario y equipo de oficina por inversiones",
                "Value" => "I02"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Equipo de transporte",
                "Value" => "I03"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Equipo de computo y accesorios",
                "Value" => "I04"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Dados, troqueles, moldes, matrices y herramental",
                "Value" => "I05"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Comunicaciones telefónicas",
                "Value" => "I06"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Comunicaciones satelitales",
                "Value" => "I07"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Otra maquinaria y equipo",
                "Value" => "I08"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Por Definir",
                "Value" => "P01"
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Sin efectos fiscales",
                "Value" => "S01"
            ]
        ];
    }

    public function getProductServiceKeyOptions()
    {
        return [
            '80161500' => '80161500 - Servicios de control de plagas',
            '43211506' => '43211506 - Fumigación',
            '56121000' => '56121000 - Servicios de jardinería',
            '56111000' => '56111000 - Servicios de limpieza',
            '81112100' => '81112100 - Servicios de mantenimiento y reparación de edificios',
        ];
    }


    public function index(Request $request)
    {
        $navigation = $this->navigation;

        // Consulta base con relaciones
        $query = Invoice::query();

        // Aplicar filtros
        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . $request->folio . '%');
        }

        if ($request->filled('customer')) {
            $ic_ids = InvoiceCustomer::where('comercial_name', 'like', '%' . $request->customer . '%')->pluck('id');
            $query->whereIn('invoice_customer_id', $ic_ids);
        }

        if ($request->filled('social_reason')) {
            $query->where('receiver_name', 'like', '%' . $request->social_reason . '%');
        }

        if ($request->filled('rfc')) {
            $query->where('receiver_rfc', 'like', '%' . $request->rfc . '%');
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('issued_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginación
        $perPage = $request->size ?? 25;
        $invoices = $query->paginate($perPage);

        // Datos adicionales para los filtros
        $invoice_customers = InvoiceCustomer::all();
        $payments = $this->getPayments();
        $currentMonth = now()->locale('es')->translatedFormat('F');

        $invoices->appends($request->except('page'));

        $paymentForms = $this->paymentForms;
        $paymentMethods = $this->paymentMethods;
        $status = $this->status;
        $cfdiTypes = $this->cfdiTypes;

        return view('invoices.index', compact(
            'navigation',
            'invoice_customers',
            'payments',
            'currentMonth',
            'invoices',
            'paymentForms',
            'paymentMethods',
            'status',
            'cfdiTypes'
        ));
    }

    public function dashboard()
    {
        $navigation = $this->navigation;
        $payments = $this->getPayments();
        $invoices = $this->getInvoices();
        $customers = $this->getCustomerStats(); // Nueva función
        $recentPayments = $this->getRecentPayments(); // Nueva función
        $monthlyData = $this->getMonthlyData(); // Nueva función
        $currentMonth = now()->locale('es')->translatedFormat('F');

        return view('invoices.dashboard', compact(
            'navigation',
            'payments',
            'currentMonth',
            'invoices',
            'customers',
            'recentPayments',
            'monthlyData'
        ));
    }

    public function getCustomerStats()
    {
        return [
            'total' => InvoiceCustomer::count(),
            'facturable' => InvoiceCustomer::where('status', 'facturable')->count(),
            'moroso' => InvoiceCustomer::where('status', 'moroso')->count(),
            'no_facturable' => InvoiceCustomer::where('status', 'no_facturable')->count(),
        ];
    }

    public function getRecentPayments()
    {
        return InvoicePayment::with(['invoice.customer'])
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();
    }

    public function getMonthlyData()
    {
        $months = [];
        $amounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->locale('es')->translatedFormat('M');
            $amounts[] = Invoice::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');
        }

        return [
            'months' => $months,
            'amounts' => $amounts
        ];
    }

    public function getPayments()
    {
        $payments = [
            'total' => InvoicePayment::whereBetween('payment_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('amount'),
            'pending' => Invoice::where('status', 'pending')->whereBetween('due_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count(),
        ];
        return $payments;
    }

    public function getInvoices()
    {
        $invoices = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'monthlyAmount' => Invoice::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('total'),
            'pending' => Invoice::where('status', 'pending')->whereBetween('due_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'cancelled' => Invoice::where('status', 'cancelled')->count()
        ];
        return $invoices;
    }

    public function getEvents(Request $request)
    {
        $events = collect();

        // Obtener los InvoiceCustomer con payment_days
        $customerTaxData = InvoiceCustomer::with('customer')
            ->whereNotNull('payment_days')
            ->where('status', '!=', 'no_facturable')
            ->get();

        foreach ($customerTaxData as $taxData) {
            $paymentDays = $taxData->payment_days;

            if ($paymentDays && is_array($paymentDays)) {
                foreach ($paymentDays as $day) {
                    // Generar eventos para el mes actual y siguiente
                    $currentMonth = now();
                    $nextMonth = now()->addMonth();

                    foreach ([$currentMonth, $nextMonth] as $month) {
                        // Validar que el día existe en el mes
                        if ($day <= $month->daysInMonth()) {
                            $eventDate = $month->copy()->day($day);

                            $color = match ($taxData->status) {
                                'facturable' => '#28a745', // Verde
                                'moroso' => '#dc3545',     // Rojo
                                default => '#ffc107'       // Amarillo
                            };

                            $events->push([
                                'id' => 'payment_' . $taxData->id . '_' . $eventDate->format('Y-m-d'),
                                'title' => 'Pago: ' . $taxData->customer->name,
                                'start' => $eventDate->format('Y-m-d'),
                                'backgroundColor' => $color,
                                'borderColor' => $color,
                                'allDay' => true,
                                'extendedProps' => [
                                    'type' => 'payment_day',
                                    'customer_id' => $taxData->customer_id,
                                    'customer_name' => $taxData->customer->name,
                                    'payment_method' => $taxData->getPaymentMethod(),
                                    'status' => $taxData->status,
                                    'frequency' => $taxData->payment_frequency
                                ]
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json($events);
    }

    public function create(Request $request)
    {
        $navigation = $this->navigation;
        $invoiceCustomers = InvoiceCustomer::all();
        $invoiceConcepts = InvoiceConcept::all();
        $paymentForms = Invoice::getPaymentFormOptions();
        $cfdiTypes = $this->cfdiTypes;
        $cfdiUsages = $this->cfdiUsages;

        $order_id = $request->input('order_id') ?? null;
        $url_action = route('invoices.store');

        return view('invoices.create', compact(
            'navigation',
            'invoiceCustomers',
            'invoiceConcepts',
            'paymentForms',
            'order_id',
            'url_action',
            'cfdiTypes',
            'cfdiUsages'
        ));
    }

    public function edit($id)
    {
        $invoice_services = [];
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $url_action = route('invoices.update');

        if ($invoice->status == 5) {
            return redirect()->route('invoices.show', ['id' => $id]);
        }

        // Solo permitir editar facturas pendientes
        /*if ($invoice->status != 0) {
            return redirect()->route('invoices.show', $id)
                ->with('error', 'Solo se pueden editar facturas en estado pendiente.');
        }*/

        $navigation = $this->navigation;
        $cfdiTypes = $this->cfdiTypes;
        $cfdiUsages = $this->cfdiUsages;
        $invoiceCustomers = InvoiceCustomer::all();
        $invoiceConcepts = InvoiceConcept::all();
        $paymentForms = Invoice::getPaymentFormOptions();

        foreach ($invoice->items as $item) {
            $invoice_services[] = [
                "quantity" => $item->quantity,
                "concept_id" => $item->concept_id,
                "product_key" => $item->product_code,
                "description" => $item->description,
                "unit_code" => $item->unit_code,
                "unit" => $this->unitCodes[$item->unit_code] ?? 'Actividad',
                "amount" => $item->unit_price,
                "discount_rate" => $item->discount_rate,
                "tax_total" => $item->tax_total,
                "subtotal" => $item->subtotal,
                "total" => $item->total
            ];
        }

        $preloadedData = [
            "invoice_id" => $invoice->id,
            "order_id" => $invoice->order_id,
            'cfdi_type' => $invoice->cfdi_type ?? 'I',
            "issued_date" => $invoice->issued_date,
            "due_date" => $invoice->due_date,
            "invoice_customer_id" => $invoice->invoice_customer_id,
            "services" => $invoice_services,
            "payment_method" => $invoice->payment_method,
            "payment_form" => $invoice->payment_form,
            "currency" => $invoice->currency,
            "exchange_rate" => '1.0',
            "tax" => $invoice->tax,
            "total" => $invoice->total
        ];



        return view('invoices.create', compact(
            'invoice',
            'navigation',
            'invoiceCustomers',
            'invoiceConcepts',
            'paymentForms',
            'preloadedData',
            'url_action',
            'cfdiTypes',
            'cfdiUsages'
        ));
    }

    public function update(Request $request)
    {
        $updated_items = [];

        //dd($request->all());
        $invoice_customer = InvoiceCustomer::findOrFail($request->invoice_customer_id);

        if (!$invoice_customer) {
            return back()->with('error', 'Cliente no encontrado.');
        }

        $invoice = Invoice::findOrFail($request->invoice_id);
        $invoice->update($request->all());
        $items = $request->services;

        $invoice->receiver_name = $invoice_customer->social_reason;
        $invoice->receiver_rfc = $invoice_customer->rfc;
        $invoice->receiver_cfdi_use = $invoice_customer->cfdi_usage;
        $invoice->receiver_fiscal_regime = $invoice_customer->tax_system;
        $invoice->receiver_tax_zip_code = $invoice_customer->zip_code;
        $invoice->save();


        foreach ($items as $item) {
            $concept = InvoiceConcept::find($item['concept_id']);
            $discount_rate = $item['discount_rate'] ? ((float) $item['discount_rate'] / 100) : 0;
            $subtotal = $item['amount'] * $item['quantity'];
            $tax_rate = $item['tax_rate'] ?? 0.16;
            $discount = $subtotal * $discount_rate;

            $subtotal = $subtotal - $discount;
            $tax_base = $subtotal;

            $tax_total = $tax_base * $tax_rate;
            $total = $subtotal + $tax_total;

            $invoice_item = InvoiceItem::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'concept_id' => $item['concept_id']
                ],
                [
                    'quantity' => (int) $item['quantity'],
                    //'name' => $concept->name,
                    'product_code' => $item['product_key'],
                    'unit_code' => $item['unit_code'],
                    'unit' => $this->unitCodes[$concept->unit_code] ?? 'Actividad',
                    //'description' => $concept->description,
                    'identification_number' => null,
                    'unit_price' => round((float) $item['amount'], 2),
                    'subtotal' => round((float) $subtotal, 2),
                    'discount_rate' => round((float) $discount_rate, 2),
                    'tax_object' => $concept->tax_object,
                    'tax_name' => 'IVA',
                    'tax_rate' => (float) $tax_rate,
                    'tax_total' => round((float) $tax_total, 2),
                    'tax_base' => round((float) $tax_base, 2),
                    'tax_is_retention' => false,
                    'tax_is_federal_tax' => true,
                    'total' => $total,
                ]
            );

            $updated_items[] = $invoice_item->id;
        }

        InvoiceItem::where('invoice_id', $invoice->id)->whereNot('id', $updated_items)->delete();

        // Solo permitir editar facturas pendientes

        /*if ($invoice->status != 0) {
            return redirect()->route('invoices.show', $id)
                ->with('error', 'Solo se pueden editar facturas en estado pendiente.');
        }*/

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Factura actualizada correctamente.');

    }

    public function store(Request $request)
    {
        try {

            $invoice_customer = InvoiceCustomer::findOrFail($request->invoice_customer_id);

            if (!$invoice_customer) {
                return back()->with('error', 'Cliente no encontrado.');
            }

            //dd($request->all());
            $next_id = Invoice::max('id') + 1;
            $invoice = new Invoice();
            $invoice->fill($request->all());
            $invoice->folio = $this->generateFolio($next_id);
            $invoice->serie = 'I';
            $invoice->expedition_place = config('services.sat.zip_code');
            $invoice->status = '01'; // Pendiente

            $invoice->receiver_name = $invoice_customer->social_reason;
            $invoice->receiver_rfc = $invoice_customer->rfc;
            $invoice->receiver_cfdi_use = $invoice_customer->cfdi_usage;
            $invoice->receiver_fiscal_regime = $invoice_customer->tax_system;
            $invoice->receiver_tax_zip_code = $invoice_customer->zip_code;

            $invoice->save();

            $items = $request->services;
            foreach ($items as $item) {
                $concept = InvoiceConcept::find($item['concept_id']);
                $discount_rate = $item['discount_rate'] ? ((float) $item['discount_rate'] / 100) : 0;
                $subtotal = $item['amount'] * $item['quantity'];
                $tax_rate = $item['tax_rate'] ?? 0.16;
                $discount = $subtotal * $discount_rate;

                $subtotal = $subtotal - $discount;
                $tax_base = $subtotal;

                $tax_total = $tax_base * $tax_rate;
                $total = $subtotal + $tax_total;

                $invoice_item = [
                    'invoice_id' => $invoice->id,
                    'concept_id' => (int) $item['concept_id'],
                    'quantity' => (int) $item['quantity'],
                    'name' => $concept->name,
                    'product_code' => $item['product_key'],
                    'unit_code' => $concept->unit_code,
                    'unit' => $this->unitCodes[$concept->unit_code] ?? 'Actividad',
                    'description' => $concept->description,
                    'identification_number' => null,
                    'unit_price' => round((float) $item['amount'], 2),
                    'subtotal' => round((float) $subtotal, 2),
                    'discount_rate' => round((float) $discount_rate, 2),
                    'tax_object' => $concept->tax_object,
                    'tax_name' => 'IVA',
                    'tax_rate' => (float) $tax_rate,
                    'tax_total' => round((float) $tax_total, 2),
                    'tax_base' => round((float) $tax_base, 2),
                    'tax_is_retention' => false,
                    'tax_is_federal_tax' => true,
                    'total' => $total,
                ];

                InvoiceItem::create($invoice_item);
            }

            return redirect()->route('invoices.show', ['id' => $invoice->id])
                ->with('success', 'Factura creada exitosamente. Ahora puedes generar el XML.');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('invoices.index')
                ->with('error', 'Error al crear la factura: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $navigation = $this->navigation;
        $cfdiTypes = $this->cfdiTypes;
        $invoice = Invoice::findOrFail($id);

        return view('invoices.show', compact('navigation', 'invoice', 'cfdiTypes'));

    }

    public function confirmData($id, string $type, Request $request)
    {
        $source = $type === 'order' ? Order::findOrFail($id) : Contract::findOrFail($id);

        if ($source->invoice) {
            return redirect()->route('invoices.show', ['id' => $source->id, 'type' => $type]);
        }

        try {
            $customer = Customer::find($source->customer_id);
            $customerTaxData = InvoiceCustomer::where('customer_id', $customer->id)->first();

            if (!$customerTaxData) {
                return redirect()->route('invoices.customers')->with('error', 'El cliente no tiene datos fiscales completos. Por favor, actualice la informacion fiscal del cliente.');
            }

            $invoice = new Invoice();
            // Generar folio solo con números
            $nextId = Invoice::max('id') + 1;
            $invoice->folio = str_pad($nextId, 6, '0', STR_PAD_LEFT); // Solo números: 000042
            $invoice->serie = 'I';
            $invoice->UUID = $source->id; // UUID provisional
            $invoice->type = 'income';
            $invoice->customer_id = $source->customer_id;
            if ($type === 'contract') {
                $invoice->contract_id = $source->id;
            }
            if ($type === 'order') {
                $invoice->order_id = $source->id;
            }
            $invoice->issue_date = Carbon::now();
            $invoice->due_date = $customerTaxData->payment_end_date ?? $invoice->issue_date->addDays(30);
            $invoice->expedition_place = config('services.sat.zip_code'); // Solo código postal

            // manejar logica del subtotal y total
            $invoice->subtotal = 0;
            foreach ($request->services as $service) {
                $invoice->subtotal += $service['cost'] * $service['quantity'];
            }

            $invoice->tax = 16;
            $invoice->total = $invoice->subtotal + ($invoice->subtotal * $invoice->tax / 100);
            $invoice->currency = $request->currency ?? 'MXN';

            $invoice->notes = $type === 'order'
                ? 'Factura generada desde la orden de servicio #' . $source->id
                : 'Factura generada desde el contrato #' . $source->id;

            $invoice->status = 1; // pendiente  
            $invoice->cfdi_use = $customerTaxData->cfdiUsage->code . '-' . $customerTaxData->cfdiUsage->description;
            $invoice->payment_type = $request->payment_form;
            $invoice->payment_method = $request->payment_method;
            $invoice->csd_serial_number = '';
            $invoice->xml_file = '';
            $invoice->pdf_path = '';

            $invoice->save();

            // agregar los items de la orden o contrato a la factura
            foreach ($request->services as $service) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_id' => $type === 'order' ? $source->id : $source->orders->first()->id,
                    'service_id' => $service['id'],
                    'item_code' => $service['clave_prod_serv'] ?: '80161500', // Código SAT por defecto
                    'description' => $service['description'],
                    'quantity' => $service['quantity'],
                    'price' => $service['cost'],
                    'tax' => 0,
                    'total' => $service['cost'] * $service['quantity'],
                    'unit_code' => $service['clave_unidad'],
                ]);
            }

            return redirect()->route('invoices.show', ['id' => $invoice->order_id ? $invoice->order_id : $invoice->contract_id, 'type' => $type])
                ->with('success', 'Factura y XML generados exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }

    public function updateData($id, string $type, Request $request)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            // Actualizar datos de la factura
            $invoice->subtotal = 0;
            foreach ($request->services as $service) {
                $invoice->subtotal += $service['cost'] * $service['quantity'];
            }

            $invoice->total = $invoice->subtotal + ($invoice->subtotal * $invoice->tax / 100);
            $invoice->currency = $request->currency ?? 'MXN';
            $invoice->payment_type = $request->payment_form;
            $invoice->payment_method = $request->payment_method;
            $invoice->save();

            // Obtener los items existentes en orden
            $existingItems = $invoice->items()->get();

            // Actualizar solo los items existentes
            foreach ($request->services as $index => $service) {
                if (isset($existingItems[$index])) {
                    $existingItems[$index]->update([
                        'item_code' => $service['clave_prod_serv'] ?: '80161500',
                        'description' => $service['description'],
                        'quantity' => $service['quantity'],
                        'price' => $service['cost'],
                        'tax' => 0,
                        'total' => $service['cost'] * $service['quantity'],
                        'unit_code' => $service['clave_unidad'],
                    ]);
                }
            }

            return redirect()->route('invoices.show', ['id' => $invoice->order_id ? $invoice->order_id : $invoice->contract_id, 'type' => $type])
                ->with('success', 'Factura actualizada exitosamente.');

        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('invoices.index')
                ->with('error', 'Error al actualizar la factura: ' . $e->getMessage());
        }
    }

    public function generateXML($id)
    {
        $invoice = Invoice::findOrFail($id);

        try {
            $this->cfdiService->generateXml($invoice);
        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                ->with('error', 'Factura creada pero hubo un error al generar el XML: ' . $e->getMessage());
        }

        return redirect()->route('invoices.show', ['id' => $invoice->id])
            ->with('success', 'Factura y XML generados exitosamente.');
    }

    public function showXml($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Si la factura ya esta 5, usar el XML de facturama
        if ($invoice->status == '5') {
            // Lógica para obtener el XML desde Facturama
            return redirect()->route('invoices.index')
                ->with('error', 'Funcionalidad de descarga de XML desde Facturama no implementada aún.');
        }

        $path = $invoice->xml_file ?: storage_path('app/invoices/xml/' . $invoice->folio . '.xml');

        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path, $invoice->folio . '.xml', [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function generatePDF($id)
    {
        $invoice = Invoice::findOrFail($id);
        $pdfPath = storage_path('app/invoices/pdf/' . $invoice->folio . '.pdf');
        $cfdiTypes = $this->cfdiTypes;

        if (file_exists($pdfPath)) {
            // Si el PDF ya existe, solo mostrarlo
            return redirect()->route('invoices.showPdf', ['invoiceId' => $invoice->id, 'cfdiTypes' => $cfdiTypes]);
        }

        try {
            $pdf = PDF::loadView('invoices.pdf_preview', compact('invoice'));
            $pdf->save($pdfPath);

            $invoice->pdf_path = $pdfPath;
            $invoice->save();

            return redirect()->route('invoices.show.pdf', ['invoiceId' => $invoice->id])
                ->with('success', 'PDF generado exitosamente.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('invoices.index')
                ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    public function showPdf($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        // dd($invoice);

        $path = $invoice->pdf_path ?: storage_path('app/invoices/pdf/' . $invoice->folio . '.pdf');
        if (!file_exists($path)) {
            $this->generatePDF($invoiceId);
        }
        return response()->download($path, $invoice->folio . '.pdf', [
            'Content-Type' => 'application/pdf'
        ]);
    }

    public function showCustomerInvoices($id)
    {
        $navigation = [
            'Dashboard' => route('invoices.index'),
            'Facturas' => route('invoices.index'),
            'Clientes' => route('invoices.customers'),
        ];

        $customer = InvoiceCustomer::findOrFail($id);
        $invoices = $customer->customer->invoices;

        return view('invoices.clients.show', compact('customer', 'invoices', 'navigation'));
    }

    public function stampInvoice(string $id)
    {
        try {
            $fac_service = new FacturamaService($this->facturama_user, $this->facturama_password);
            $response = $fac_service->createInvoice($id);

            if ($response['success'] && $response['data'] != null) {
                $data = $response['data'];

                $invoice = Invoice::find($id);
                $invoice->update([
                    'status' => 5,
                    'facturama_token' => $data->Id,
                    'UUID' => $data->Complement->TaxStamp->Uuid,
                    'stamped_date' => $data->Complement->TaxStamp->Date,
                    'cfdi_sign' => $data->Complement->TaxStamp->CfdiSign,
                    'sat_cert_number' => $data->Complement->TaxStamp->SatCertNumber,
                    'sat_sign' => $data->Complement->TaxStamp->SatSign,
                    'rfc_prov_cert' => $data->Complement->TaxStamp->RfcProvCertif,
                    'csd_serial_number' => $data->CertNumber
                ]);
                return back()->with('success', $response['message']);
            } else {
                return back()->with('error', $response['message']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return back()->with('error', 'Error al timbrar la factura: ' . $e->getMessage());
        }
    }


    public function stampCreditNote(string $id)
    {
        try {
            $fac_service = new FacturamaService($this->facturama_user, $this->facturama_password);
            $response = $fac_service->createCreditNote($id);
            if ($response['success'] && $response['data'] != null) {
                $data = $response['data'];
                $credit_note = CreditNote::find($id);
                $credit_note->update([
                    'status' => 5,
                    'facturama_token' => $data->Id,
                    'UUID' => $data->Complement->TaxStamp->Uuid,
                    'stamped_at' => now()->format('Y-m-d'),
                ]);

                return back()->with('success', $response['message']);
            } else {
                return back()->with('error', $response['message']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return back()->with('error', 'Error al timbrar la factura: ' . $e->getMessage());
        }
    }

    public function stampPayment(string $id)
    {
        try {
            $fac_service = new FacturamaService($this->facturama_user, $this->facturama_password);
            $response = $fac_service->createPayment($id);
            if ($response['success'] && $response['data'] != null) {
                $data = $response['data'];
                $payment = Payment::find($id);
                $payment->update([
                    'status' => 5,
                    'facturama_token' => $data->Id,
                    'UUID' => $data->Complement->TaxStamp->Uuid,
                    'stamped_at' => now()->format('Y-m-d'),
                ]);
                return back()->with('success', $response['message']);
            } else {
                return back()->with('error', $response['message']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return back()->with('error', 'Error al timbrar la factura: ' . $e->getMessage());
        }
    }

    public function sendInvoice(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $contractId = $request->input('contract_id');

            if ($orderId) {
                $order = Order::with('customer', 'invoice')->findOrFail($orderId);
                $invoice = $order->invoice;
                $customerEmail = $order->customer->email;
                $contract = null;
            } elseif ($contractId) {
                $contract = Contract::with('customer', 'invoice')->findOrFail($contractId);
                $invoice = $contract->invoice;
                $customerEmail = $contract->customer->email;
                $order = null;
            } else {
                return redirect()->back()->with('error', 'No se encontró la orden o contrato.');
            }

            // Enviar el email
            Mail::to($customerEmail)->send(new InvoiceSent($invoice, $order, $contract));

            // Actualizar el estado de la factura como envia                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            da (opcional)
            $invoice->update(['status' => 7]); // 7 = enviada

            return redirect()->back()->with('success', 'Factura enviada correctamente a ' . $customerEmail);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al enviar la factura: ' . $e->getMessage());
        }
    }

    function getConcepts(Request $request)
    {
        $concepts_query = InvoiceConcept::query();

        if ($request->filled('product_key')) {
            $concepts_query->where('product_key', 'like', '%' . $request->product_key . '%');
        }

        if ($request->filled('identificator')) {
            $concepts_query->where('identification_number', 'like', '%' . $request->identificator . '%');
        }

        if ($request->filled('customer')) {
            $concepts_query->where('name', 'like', '%' . $request->customer . '%');
        }

        if ($request->filled('unit_code')) {
            $concepts_query->where('unit_code', $request->unit_code);
        }

        $concepts = $concepts_query->orderBy('name', $request->direction ?? 'asc')->paginate($request->size ?? 25)->appends($request->all());

        $navigation = $this->navigation;
        $unitCodes = $this->unitCodes;
        $taxObjects = $this->taxObjects;
        $paymentForms = $this->paymentForms;
        $paymentMethods = $this->paymentMethods;

        return view('invoices.concepts.index', compact('navigation', 'unitCodes', 'concepts', 'taxObjects', 'paymentForms', 'paymentMethods'));
    }

    function storeConcept(Request $request)
    {
        $concept = new InvoiceConcept();
        $concept->fill($request->all());
        $concept->tax_rate = number_format($request->tax_rate / 100, 2);
        $concept->save();
        return redirect()->back();
    }


    function updateConcept(Request $request)
    {
        $concept_id = $request->concept_id;
        $concept = InvoiceConcept::find($concept_id);
        $concept->update($request->except('concept_id'));
        $concept->tax_rate = number_format($request->tax_rate / 100, 2);
        $concept->save();
        return redirect()->back();
    }

    public function downloadInvoice(string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $facturamaService = new FacturamaService($this->facturama_user, $this->facturama_password);
        $pdf_response = $facturamaService->getInvoiceFormat('pdf', 'issued', $invoice->facturama_token);
        $xml_response = $facturamaService->getInvoiceFormat('xml', 'issued', $invoice->facturama_token);

        $zipFileName = 'factura_' . time() . '.zip';

        // Crear ZIP en storage
        $zipPath = Storage::disk('local')->path('temp/' . $zipFileName);
        $filename = 'factura_' . $invoice->folio . '_' . $invoice->issued_date;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($pdf_response['full_path'], $filename . '.pdf');
            $zip->addFile($xml_response['full_path'], $filename . '.xml');
            $zip->close();

            return response()->download($zipPath, $zipFileName)
                ->deleteFileAfterSend(true);
        }
        return response()->json(['error' => 'Error creando ZIP'], 500);
    }

    public function downloadCreditNote(string $id)
    {
        $credit_note = CreditNote::findOrFail($id);
        $facturamaService = new FacturamaService($this->facturama_user, $this->facturama_password);
        $pdf_response = $facturamaService->getInvoiceFormat('pdf', 'issued', $credit_note->facturama_token);
        $xml_response = $facturamaService->getInvoiceFormat('xml', 'issued', $credit_note->facturama_token);

        $zipFileName = 'nota_credito_' . time() . '.zip';

        // Crear ZIP en storage
        $zipPath = Storage::disk('local')->path('temp/' . $zipFileName);
        $filename = 'nota_credito_' . $credit_note->folio . '_' . $credit_note->stamped_at;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($pdf_response['full_path'], $filename . '.pdf');
            $zip->addFile($xml_response['full_path'], $filename . '.xml');
            $zip->close();

            return response()->download($zipPath, $zipFileName)
                ->deleteFileAfterSend(true);
        }
        return response()->json(['error' => 'Error creando ZIP'], 500);
    }

    public function downloadPayment(string $id)
    {
        $payment = Payment::findOrFail($id);
        $facturamaService = new FacturamaService($this->facturama_user, $this->facturama_password);
        $pdf_response = $facturamaService->getInvoiceFormat('pdf', 'issued', $payment->facturama_token);
        $xml_response = $facturamaService->getInvoiceFormat('xml', 'issued', $payment->facturama_token);

        $zipFileName = 'complemento_pago_' . time() . '.zip';

        // Crear ZIP en storage
        $zipPath = Storage::disk('local')->path('temp/' . $zipFileName);
        $filename = 'complemento_pago_' . $payment->folio . '_' . $payment->stamped_at;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($pdf_response['full_path'], $filename . '.pdf');
            $zip->addFile($xml_response['full_path'], $filename . '.xml');
            $zip->close();

            return response()->download($zipPath, $zipFileName)
                ->deleteFileAfterSend(true);
        }
        return response()->json(['error' => 'Error creando ZIP'], 500);
    }


    public function payments(string $id)
    {

    }

    public function indexCreditNotes(Request $request)
    {
        //dd($request->all());
        $query = CreditNote::query();
        $invoice = null;

        // Aplicar filtros
        if ($request->filled('note_folio')) {
            $query->where('folio', 'like', '%' . $request->note_folio . '%');
        }

        if ($request->filled('invoice_folio')) {
            $folio_parts = preg_split('/[-\\s]+/', $request->invoice_folio);
            $invoice_ids = Invoice::where('serie', 'like', '%' . $folio_parts[0] . '%')->where('folio', 'like', '%' . $folio_parts[1] . '%')->pluck('id');
            $query->whereIn('invoice_id', $invoice_ids);
        }

        if ($request->filled('customer')) {
            $ic_ids = InvoiceCustomer::where('comercial_name', 'like', '%' . $request->customer . '%')->pluck('invoice_id');
            $query->whereIn('invoice_id', $ic_ids);
        }

        if ($request->filled('social_reason')) {
            $query->where('receiver_name', 'like', '%' . $request->social_reason . '%');
        }

        if ($request->filled('rfc')) {
            $query->where('receiver_rfc', 'like', '%' . $request->rfc . '%');
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('stamped_at', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->invoice_id) {
            $query->where('invoice_id', $request->invoice_id);
            $invoice = Invoice::findOrFail($request->invoice_id);
            $request->merge(['invoice_folio' => $invoice->serie . '-' . $invoice->folio]);
        }

        // Paginación
        $perPage = $request->size ?? 25;
        $credit_notes = $query->paginate($perPage)->appends($request->except('page'));
        $success = "Se encontraron coincidencias";

        $status = $this->status;

        $navigation = $this->navigation;

        return view('invoices.credit-notes.index', compact('credit_notes', 'navigation', 'status'));
    }

    public function createCreditNote(Request $request)
    {
        $invoices_data = [];
        //dd($request->all());
        $paymentForms = $this->paymentForms;
        $paymentMethods = $this->paymentMethods;
        $cfdiUsages = $this->cfdiUsages;
        $taxRegimes = $this->taxRegimes;
        $status = $this->status;
        $navigation = $this->navigation;

        $sat_config = config('services.sat');
        //dd($sat_config);

        return view('invoices.credit-notes.create', compact('navigation', 'paymentForms', 'paymentMethods', 'cfdiUsages', 'taxRegimes', 'status', 'sat_config'));
    }

    public function storeCreditNotes(Request $request)
    {
        //dd($request->all());
        $creditNotes_items = [];
        $next_id = CreditNote::max('id') + 1;
        $invoice = Invoice::findOrFail($request->invoice_id);
        $credit_note = new CreditNote();
        $credit_note->fill($request->all());
        $credit_note->status = 1;
        $credit_note->type = 'E';
        $credit_note->serie = 'E';
        $credit_note->folio = $this->generateFolio($next_id);
        $credit_note->save();

        $items = $request->items;
        foreach ($items as $index => $item) {
            $concept = InvoiceConcept::find($index);
            CreditNoteItem::create([
                'credit_note_id' => $credit_note->id,
                'quantity' => $item['quantity'],
                'name' => $concept->name,
                'product_code' => $item['product_code'],
                'unit' => $this->unitCodes[$concept->unit_code],
                'unit_code' => $item['unit_code'],
                'description' => $item['description'],
                'identification_number' => null,
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'discount_rate' => $item['discount_percent'] / 100,
                'tax_name' => 'IVA',
                'tax_rate' => $item['tax_rate'],
                'tax_object' => $concept->tax_object,
                'tax_total' => $item['tax_total'],
                'tax_base' => $item['subtotal'],
                'tax_is_retention' => false,
                'tax_is_federal_tax' => true,
                'total' => $item['total'],
            ]);
        }

        return redirect()->route('invoices.credit-notes.index', ['id' => $invoice->id])
            ->with('success', 'Nota de crédito creada exitosamente.');
    }

    public function parseFolio($folioString)
    {
        $folioString = trim($folioString);

        // Caso: vacío
        if (empty($folioString)) {
            return ['serie' => '', 'folio' => ''];
        }

        // Dividir por guiones, espacios, puntos, etc.
        $parts = preg_split('/[-\s\.]+/', $folioString);

        // Filtrar partes vacías
        $parts = array_filter($parts, function ($part) {
            return !empty($part);
        });

        // Reindexar array
        $parts = array_values($parts);

        if (count($parts) === 2) {
            return [
                'serie' => $parts[0],
                'folio' => $parts[1]
            ];
        }
        // Si hay más de 2 partes, unir las primeras como serie
        elseif (count($parts) > 2) {
            $serie = implode('-', array_slice($parts, 0, -1));
            $folio = end($parts);
            return [
                'serie' => $serie,
                'folio' => $folio
            ];
        }
        // Si solo hay una parte
        else {
            // Patrón: letras opcionales + números (mejorado para más casos)
            if (preg_match('/^([A-Za-z]*)(\d+)$/', $folioString, $matches)) {
                return [
                    'serie' => $matches[1],
                    'folio' => $matches[2]
                ];
            }
            // Patrón: solo números
            elseif (preg_match('/^\d+$/', $folioString)) {
                return [
                    'serie' => '',
                    'folio' => $folioString
                ];
            }
            // Patrón: solo letras
            elseif (preg_match('/^[A-Za-z]+$/', $folioString)) {
                return [
                    'serie' => $folioString,
                    'folio' => ''
                ];
            }
            // Cualquier otro caso
            else {
                return [
                    'serie' => '',
                    'folio' => $folioString
                ];
            }
        }
    }

    public function searchInvoices(Request $request)
    {
        $invoices = [];
        try {
            $query = Invoice::query();


            $folioData = $this->parseFolio($request->invoice_folio);

            // Filtro por folio
            if ($request->filled('invoice_folio')) {
                $folioData = $this->parseFolio($request->invoice_folio);
                if (!empty($folioData['serie'])) {
                    $query->where('serie', 'LIKE', "%{$folioData['serie']}%");
                }

                if (!empty($folioData['folio'])) {
                    $query->where('folio', 'LIKE', "%{$folioData['folio']}%");
                }
            }

            // Filtro por razón social
            if ($request->filled('social_reason')) {
                $query->where('receiver_name', 'like', '%' . $request->social_reason . '%');
            }

            // Filtro por RFC
            if ($request->filled('rfc')) {
                $query->where('receiver_rfc', 'like', '%' . $request->rfc . '%');
            }

            // Filtro por fecha
            if ($request->filled('issued_date')) {
                $query->whereDate('issued_date', $request->issued_date);
            }

            $invoices = $query->select('id', 'serie', 'folio', 'receiver_name', 'receiver_rfc', 'receiver_tax_zip_code', 'receiver_cfdi_use', 'receiver_fiscal_regime', 'issued_date', 'total', 'UUID')->get();

            foreach ($invoices as $invoice) {
                $invoices_data[] = [
                    'id' => $invoice->id,
                    'serie' => $invoice->serie,
                    'folio' => $invoice->folio,
                    'receiver_name' => $invoice->receiver_name,
                    'receiver_rfc' => $invoice->receiver_rfc,
                    'receiver_tax_zip_code' => $invoice->receiver_tax_zip_code,
                    'receiver_cfdi_use' => $invoice->receiver_cfdi_use,
                    'receiver_fiscal_regime' => $invoice->receiver_fiscal_regime,
                    'issued_date' => $invoice->issued_date,
                    'total' => $invoice->total,
                    'UUID' => $invoice->UUID,
                    'status' => $invoice->status,
                    'items' => $invoice->items()->get()
                ];
            }

            $invoices = $invoices_data;
            return response()->json([
                'success' => true,
                'invoices' => $invoices
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }


    public function indexPayments(Request $request)
    {
        //dd($request->all());
        $query = Payment::query();
        $invoice = null;

        // Aplicar filtros
        /*if ($request->filled('note_folio')) {
            $query->where('folio', 'like', '%' . $request->note_folio . '%');
        }

        if ($request->filled('invoice_folio')) {
            $folio_parts = preg_split('/[-\\s]+/', $request->invoice_folio);
            $invoice_ids = Invoice::where('serie', 'like', '%' . $folio_parts[0] . '%')->where('folio', 'like', '%' . $folio_parts[1] . '%')->pluck('id');
            $query->whereIn('invoice_id', $invoice_ids);
        }

        if ($request->filled('customer')) {
            $ic_ids = InvoiceCustomer::where('comercial_name', 'like', '%' . $request->customer . '%')->pluck('invoice_id');
            $query->whereIn('invoice_id', $ic_ids);
        }

        if ($request->filled('social_reason')) {
            $query->where('receiver_name', 'like', '%' . $request->social_reason . '%');
        }

        if ($request->filled('rfc')) {
            $query->where('receiver_rfc', 'like', '%' . $request->rfc . '%');
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('stamped_at', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->invoice_id) {
            $query->where('invoice_id', $request->invoice_id);
            $invoice = Invoice::findOrFail($request->invoice_id);
            $request->merge(['invoice_folio' => $invoice->serie . '-' . $invoice->folio]);
        }*/

        // Paginación
        $perPage = $request->size ?? 25;
        $payments = $query->paginate($perPage)->appends($request->except('page'));
        $success = "Se encontraron coincidencias";

        $status = $this->status;

        $navigation = $this->navigation;

        return view('invoices.payments.index', compact('payments', 'navigation', 'status'));
    }

    public function createPayment(Request $request)
    {
        $invoices_data = [];
        //dd($request->all());
        $paymentForms = $this->paymentForms;
        $paymentMethods = $this->paymentMethods;
        $cfdiUsages = $this->cfdiUsages;
        $taxRegimes = $this->taxRegimes;
        $status = $this->status;
        $navigation = $this->navigation;

        $taxObjects = $this->taxObjects;
        $sat_config = config('services.sat');

        return view('invoices.payments.create', compact('navigation', 'paymentForms', 'paymentMethods', 'cfdiUsages', 'taxRegimes', 'status', 'sat_config', 'taxObjects'));
    }

    public function storePayment(Request $request)
    {
        $data = json_decode($request->selected_invoices_data);

        try {
            $next_id = Payment::max('id') + 1;
            $pymt = Payment::create([
                'cfdi_type' => $data->CfdiType,
                'facturama_token' => null,
                'UUID' => null,
                'folio' => $this->generateFolio($next_id),
                'serie' => 'P',
                'expedition_place' => $data->ExpeditionPlace,
                'receiver_name' => $data->Receiver->Name,
                'receiver_rfc' => $data->Receiver->Rfc,
                'receiver_cfdi_use' => $data->Receiver->CfdiUse,
                'receiver_fiscal_regime' => $data->Receiver->FiscalRegime,
                'receiver_tax_zip_code' => $data->Receiver->TaxZipCode,
                'status' => 1,
                'stamped_at' => null,
            ]);

            $payments_items = $data->Complemento->Payments;
            foreach ($payments_items as $pymt_item) {
                $item = PaymentItem::create([
                    'payment_id' => $pymt->id,
                    'payment_form' => $pymt_item->PaymentForm,
                    'payment_date' => Carbon::parse($pymt_item->Date)->format('Y-m-d'),
                    'amount' => $pymt_item->Amount,
                    'currency' => $pymt_item->Currency,
                ]);

                $docs = $pymt_item->RelatedDocuments;

                foreach ($docs as $doc) {
                    $taxes = $doc->Taxes[0];
                    PaymentsRelatedDocument::create([
                        'payment_item_id' => $item->id,
                        'invoice_id' => $doc->InvoiceId,
                        'cfdi_uuid' => $doc->Uuid,
                        'partiality_number' => $doc->PartialityNumber,
                        'folio' => $doc->Folio,
                        'serie' => $doc->Serie,
                        'payment_method' => $doc->PaymentMethod,
                        'previous_balance_amount' => $doc->PreviousBalanceAmount,
                        'amount_paid' => $doc->AmountPaid,
                        'imp_saldo_insoluto' => $doc->ImpSaldoInsoluto,
                        'tax_object' => $doc->TaxObject,
                        'tax_name' => $taxes->Name,
                        'tax_rate' => $taxes->Rate,
                        'tax_total' => $taxes->Total,
                        'tax_base' => $taxes->Base,
                        'tax_is_retention' => $taxes->IsRetention == "false",
                    ]);
                }
            }

            return redirect()->route('invoices.payments.index')
                ->with('success', 'Pago creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('invoices.payments.index')
                ->with('error', 'Error al crear el pago: ' . $e->getMessage());
        }
    }

    public function editPayment(string $id)
    {
        $items = [];
        $docs = [];
        $paymentComplement = Payment::findOrFail($id);

        foreach ($paymentComplement->items as $item) {
            foreach ($item->relatedDocuments as $doc) {
                $docs[] = [
                    'RelatedDocId' => $doc->id,
                    'InvoiceId' => $doc->invoice_id,
                    'TaxObject' => $doc->tax_object,
                    'Uuid' => $doc->cfdi_uuid,
                    'PartialityNumber' => $doc->partiality_number,
                    'Folio' => $doc->folio,
                    'Serie' => $doc->serie,
                    'Currency' => $item->currency,
                    'PaymentMethod' => $doc->payment_method,
                    'PreviousBalanceAmount' => $doc->previous_balance_amount,
                    'AmountPaid' => $doc->amount_paid,
                    'ImpSaldoInsoluto' => $doc->imp_saldo_insoluto,
                    'Taxes' => [
                        [
                            'Name' => $doc->tax_name,
                            'Rate' => $doc->tax_rate,
                            'Total' => $doc->tax_total,
                            'Base' => $doc->tax_base,
                            'IsRetention' => $doc->tax_is_retention,
                        ]
                    ]
                ];
            }


            $items[] = [
                'PaymentItemId' => $item->id,
                'Date' => Carbon::parse($item->payment_date)->format('Y-m-d\TH:i:s.v\Z'),
                'Amount' => $item->amount,
                'Currency' => $item->currency,
                'PaymentForm' => $item->payment_form,
                'RelatedDocuments' => $docs,
            ];
        }

        $paymentsData = [
            "CfdiType" => 'P',
            "NameId" => '14',
            "Folio" => $paymentComplement->folio,
            "ExpeditionPlace" => $paymentComplement->expedition_place,
            "Receiver" => [
                'Rfc' => $paymentComplement->receiver_rfc,
                'Name' => $paymentComplement->receiver_name,
                'CfdiUse' => $paymentComplement->receiver_cfdi_use,
                'FiscalRegime' => $paymentComplement->receiver_fiscal_regime,
                'TaxZipCode' => $paymentComplement->receiver_tax_zip_code
            ],
            "Complemento" => [
                'Payments' => $items
            ],
        ];

        $navigation = $this->navigation;
        $paymentForms = $this->paymentForms;
        $paymentMethods = $this->paymentMethods;
        $cfdiUsages = $this->cfdiUsages;
        $taxRegimes = $this->taxRegimes;
        $status = $this->status;

        $taxObjects = $this->taxObjects;
        $sat_config = config('services.sat');

        //dd(json_encode($payments));

        //dd($payments);
        return view('invoices.payments.edit', compact(
            'navigation',
            'paymentsData',
            'taxObjects',
            'sat_config',
            'cfdiUsages',
            'taxRegimes',
            'paymentForms',
            'paymentMethods',
            'paymentComplement'
        ));
    }

    public function updatePayment(Request $request, string $id)
    {
        //dd($request->all());
        $payment_id = $id;
        $data = json_decode($request->selected_invoices_data);
        //dd($data);
        $delete_items = [];
        $delete_docs = [];


        try {
            $pymt = Payment::find($payment_id);
            if ($pymt) {
                $pymt->update([
                    'expedition_place' => $data->ExpeditionPlace,
                    'receiver_name' => $data->Receiver->Name,
                    'receiver_rfc' => $data->Receiver->Rfc,
                    'receiver_cfdi_use' => $data->Receiver->CfdiUse,
                    'receiver_fiscal_regime' => $data->Receiver->FiscalRegime,
                    'receiver_tax_zip_code' => $data->Receiver->TaxZipCode,
                ]);

                $payments_items = $data->Complemento->Payments;
                foreach ($payments_items as $pymt_item) {
                    $item = PaymentItem::find($pymt_item->PaymentItemId);

                    if ($item) {
                        $item->update(
                            [
                                'payment_form' => $pymt_item->PaymentForm,
                                'payment_date' => Carbon::parse($pymt_item->Date)->format('Y-m-d'),
                                'amount' => $pymt_item->Amount,
                                'currency' => $pymt_item->Currency,
                            ]
                        );
                    }

                    $docs = $pymt_item->RelatedDocuments;

                    foreach ($docs as $doc) {
                        $taxes = $doc->Taxes[0];
                        $prdoc = PaymentsRelatedDocument::find($doc->RelatedDocId);
                        if ($prdoc) {
                            $prdoc->update([
                                'cfdi_uuid' => $doc->Uuid,
                                'partiality_number' => $doc->PartialityNumber,
                                'folio' => $doc->Folio,
                                'serie' => $doc->Serie,
                                'payment_method' => $doc->PaymentMethod,
                                'previous_balance_amount' => $doc->PreviousBalanceAmount,
                                'amount_paid' => $doc->AmountPaid,
                                'imp_saldo_insoluto' => $doc->ImpSaldoInsoluto,
                                'tax_object' => $doc->TaxObject,
                                'tax_name' => $taxes->Name,
                                'tax_rate' => $taxes->Rate,
                                'tax_total' => $taxes->Total,
                                'tax_base' => $taxes->Base,
                                'tax_is_retention' => $taxes->IsRetention == "false",
                            ]);
                        }

                        $delete_docs[] = $doc->RelatedDocId;
                    }

                    $delete_items[] = $pymt_item->PaymentItemId;
                    PaymentsRelatedDocument::whereNotIn('id', $delete_docs)->delete();
                }

                PaymentItem::whereNotIn('id', $delete_items)->delete();
            }

            return redirect()->route('invoices.payments.index')
                ->with('success', 'Pago actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('invoices.payments.index')
                ->with('error', 'Error al editar el pago: ' . $e->getMessage());
        }
    }

    public function indexPayrolls() {
        
    }

    public function createPayrolls() {
        
    }

    public function storePayrolls(Request $request)
    {
        // Validar los campos básicos
        //dd($request);
        $validated = $request->validate([
            'folio' => 'required|string|max:50|unique:payrolls,folio',
            'expedition_place' => 'required|string|max:10',
            'payment_date' => 'required|date',
            'payroll_type' => 'required|in:O,E',
            'receiver_rfc' => 'required|string|max:13',
            'receiver_name' => 'required|string|max:255',
            'receiver_cfdi_use' => 'required|string|max:5',
            'receiver_fiscal_regime' => 'required|string|max:10',
            'receiver_tax_zip_code' => 'required|string|max:10',
            'employee_curp' => 'required|string|max:18',
            'employee_social_security_number' => 'required|string|max:15',
            'employee_number' => 'required|string|max:50',
            'employee_daily_salary' => 'required|numeric|min:0',
            'initial_payment_date' => 'required|date',
            'final_payment_date' => 'required|date',
            'days_paid' => 'required|integer|min:1',
            'position_risk' => 'required|integer|between:1,5',
            'contract_type' => 'required|string|max:10',
            'regime_type' => 'required|string|max:10',
            'type_of_journey' => 'required|string|max:10',
            'frequency_payment' => 'required|string|max:10',
            'federal_entity_key' => 'required|string|max:10',
            'start_date_labor_relations' => 'required|date',
            'employer_registration' => 'required|string|max:20',
            'daily_salary' => 'required|numeric|min:0',
            'base_salary' => 'required|numeric|min:0',
        ]);

        // Validar que la fecha final sea mayor o igual a la inicial
        $initialDate = Carbon::parse($request->initial_payment_date);
        $finalDate = Carbon::parse($request->final_payment_date);
        if ($finalDate->lt($initialDate)) {
            return back()->withErrors([
                'final_payment_date' => 'La fecha final debe ser mayor o igual a la fecha inicial'
            ])->withInput();
        }

        // Validar que los días pagados sean consistentes con las fechas
        $calculatedDays = $initialDate->diffInDays($finalDate) + 1;
        if ($request->days_paid > $calculatedDays) {
            return back()->withErrors([
                'days_paid' => "Los días pagados ($request->days_paid) exceden el rango de fechas ($calculatedDays días)"
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Procesar el JSON de nómina si viene del formulario
            $payrollData = [];
            if ($request->has('payroll_data') && $request->payroll_data) {
                $payrollData = json_decode($request->payroll_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar los datos de la nómina: ' . json_last_error_msg());
                }
            }

            // Calcular totales desde los datos del formulario o del JSON
            $totals = $this->calculateTotals($request, $payrollData);

            // Crear la nómina
            $payroll = Payroll::create([
                'folio' => $request->folio,
                'cfdi_type' => 'N',
                'payment_method' => 'PUE',
                'expedition_place' => $request->expedition_place,
                'name_id' => '16',
                // Datos del receptor
                'receiver_rfc' => $request->receiver_rfc,
                'receiver_name' => $request->receiver_name,
                'receiver_cfdi_use' => $request->receiver_cfdi_use,
                'receiver_fiscal_regime' => $request->receiver_fiscal_regime,
                'receiver_tax_zip_code' => $request->receiver_tax_zip_code,
                // Datos de la nómina
                'payroll_type' => $request->payroll_type,
                'daily_salary' => $request->daily_salary,
                'base_salary' => $request->base_salary,
                'payment_date' => $request->payment_date,
                'initial_payment_date' => $request->initial_payment_date,
                'final_payment_date' => $request->final_payment_date,
                'days_paid' => $request->days_paid,

                // Datos del emisor
                'employer_registration' => $request->employer_registration,

                // Datos del empleado
                'employee_curp' => $request->employee_curp,
                'employee_social_security_number' => $request->employee_social_security_number,
                'position_risk' => $request->position_risk,
                'contract_type' => $request->contract_type,
                'regime_type' => $request->regime_type,
                'unionized' => $request->boolean('unionized'),
                'type_of_journey' => $request->type_of_journey,
                'employee_number' => $request->employee_number,
                'department' => $request->department,
                'position' => $request->position,
                'frequency_payment' => $request->frequency_payment,
                'federal_entity_key' => $request->federal_entity_key,
                'employee_daily_salary' => $request->employee_daily_salary,
                'start_date_labor_relations' => $request->start_date_labor_relations,
                // Totales
                'total_perceptions' => $totals['perceptions'],
                'total_deductions' => $totals['deductions'],
                'total_other_payments' => $totals['other_payments'],
                'total' => $totals['net'],
                'status' => 'draft',
            ]);

            // Guardar percepciones
            $this->savePerceptions($payroll, $request, $payrollData);

            // Guardar deducciones
            $this->saveDeductions($payroll, $request, $payrollData);

            // Guardar otros pagos
            $this->saveOtherPayments($payroll, $request, $payrollData);

            DB::commit();

            // Redirigir según la acción
            if ($request->has('action')) {
                switch ($request->action) {
                    case 'save_and_stamp':
                        return $this->stampPayroll($payroll);
                    case 'save_and_continue':
                        return redirect()->route('payroll.create')
                            ->with('success', 'Nómina guardada correctamente. Puedes crear otra.')
                            ->with('last_payroll', $payroll->id);
                    case 'save_and_edit':
                        return redirect()->route('payroll.edit', $payroll->id)
                            ->with('success', 'Nómina guardada correctamente. Puedes continuar editando.');
                    default:
                        return redirect()->route('payroll.show', $payroll->id)
                            ->with('success', 'Nómina guardada correctamente como borrador.');
                }
            }

            return redirect()->route('payroll.show', $payroll->id)
                ->with('success', 'Nómina guardada correctamente como borrador.');

        /*} catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;*/
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar nómina: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('payroll_data')
            ]);

            return back()->withErrors([
                'error' => 'Error al guardar la nómina: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Calcular totales de la nómina
     */
    private function calculateTotals(Request $request, array $payrollData = [])
    {
        $totals = [
            'perceptions' => 0,
            'deductions' => 0,
            'other_payments' => 0,
            'net' => 0
        ];

        // Calcular desde datos del formulario directo
        if ($request->has('perception_taxed_amount') || $request->has('perception_exempt_amount')) {
            $taxedAmounts = $request->input('perception_taxed_amount', []);
            $exemptAmounts = $request->input('perception_exempt_amount', []);

            foreach ($taxedAmounts as $index => $taxed) {
                $exempt = $exemptAmounts[$index] ?? 0;
                $totals['perceptions'] += (float) $taxed + (float) $exempt;
            }
        }

        if ($request->has('deduction_amount')) {
            foreach ($request->input('deduction_amount', []) as $amount) {
                $totals['deductions'] += (float) $amount;
            }
        }

        if ($request->has('other_payment_amount')) {
            foreach ($request->input('other_payment_amount', []) as $amount) {
                $totals['other_payments'] += (float) $amount;
            }
        }

        // Calcular desde datos JSON
        if (!empty($payrollData)) {
            if (isset($payrollData['Complemento']['Payroll']['Perceptions']['Details'])) {
                foreach ($payrollData['Complemento']['Payroll']['Perceptions']['Details'] as $perception) {
                    $totals['perceptions'] += ($perception['TaxedAmount'] ?? 0) + ($perception['ExemptAmount'] ?? 0);
                }
            }
            if (isset($payrollData['Complemento']['Payroll']['Deductions']['Details'])) {
                foreach ($payrollData['Complemento']['Payroll']['Deductions']['Details'] as $deduction) {
                    $totals['deductions'] += $deduction['Amount'] ?? 0;
                }
            }
            if (isset($payrollData['Complemento']['Payroll']['OtherPayments'])) {
                foreach ($payrollData['Complemento']['Payroll']['OtherPayments'] as $otherPayment) {
                    $totals['other_payments'] += $otherPayment['Amount'] ?? 0;
                }
            }
        }

        // Calcular neto
        $totals['net'] = $totals['perceptions'] - $totals['deductions'] + $totals['other_payments'];

        return $totals;
    }

    /**
     * Guardar percepciones
     */
    private function savePerceptions(Payroll $payroll, Request $request, array $payrollData = [])
    {
        $perceptions = [];

        // Desde datos del formulario directo
        if ($request->has('perception_type')) {
            $types = $request->input('perception_type', []);
            $codes = $request->input('perception_code', []);
            $descriptions = $request->input('perception_description', []);
            $taxedAmounts = $request->input('perception_taxed_amount', []);
            $exemptAmounts = $request->input('perception_exempt_amount', []);

            foreach ($types as $index => $type) {
                if (!empty($type) && !empty($codes[$index])) {
                    $perceptions[] = [
                        'perception_type' => $type,
                        'code' => $codes[$index],
                        'description' => $descriptions[$index] ?? '',
                        'taxed_amount' => (float)($taxedAmounts[$index] ?? 0),
                        'exempt_amount' => (float)($exemptAmounts[$index] ?? 0),
                    ];
                }
            }
        }

        // Desde datos JSON
        if (empty($perceptions) && isset($payrollData['Complemento']['Payroll']['Perceptions']['Details'])) {
            foreach ($payrollData['Complemento']['Payroll']['Perceptions']['Details'] as $perception) {
                $perceptions[] = [
                    'perception_type' => $perception['PerceptionType'],
                    'code' => $perception['Code'],
                    'description' => $perception['Description'],
                    'taxed_amount' => (float) ($perception['TaxedAmount'] ?? 0),
                    'exempt_amount' => (float) ($perception['ExemptAmount'] ?? 0),
                ];
            }
        }

        // Guardar en base de datos
        foreach ($perceptions as $perceptionData) {
            $payroll->perceptions()->create($perceptionData);
        }
    }

    /**
     * Guardar deducciones
     */
    private function saveDeductions(Payroll $payroll, Request $request, array $payrollData = [])
    {
        $deductions = [];

        // Desde datos del formulario directo
        if ($request->has('deduction_type')) {
            $types = $request->input('deduction_type', []);
            $codes = $request->input('deduction_code', []);
            $descriptions = $request->input('deduction_description', []);
            $amounts = $request->input('deduction_amount', []);

            foreach ($types as $index => $type) {
                if (!empty($type) && !empty($codes[$index])) {
                    $deductions[] = [
                        'deduction_type' => $type,
                        'code' => $codes[$index],
                        'description' => $descriptions[$index] ?? '',
                        'amount' => (float) ($amounts[$index] ?? 0),
                    ];
                }
            }
        }

        // Desde datos JSON
        if (empty($deductions) && isset($payrollData['Complemento']['Payroll']['Deductions']['Details'])) {
            foreach ($payrollData['Complemento']['Payroll']['Deductions']['Details'] as $deduction) {
                $deductions[] = [
                    'deduction_type' => $deduction['DeduccionType'],
                    'code' => $deduction['Code'],
                    'description' => $deduction['Description'],
                    'amount' => (float) ($deduction['Amount'] ?? 0),
                ];
            }
        }

        // Guardar en base de datos
        foreach ($deductions as $deductionData) {
            $payroll->deductions()->create($deductionData);
        }
    }

    /**
     * Guardar otros pagos
     */
    private function saveOtherPayments(Payroll $payroll, Request $request, array $payrollData = [])
    {
        $otherPayments = [];

        // Desde datos del formulario directo
        if ($request->has('other_payment_type')) {
            $types = $request->input('other_payment_type', []);
            $codes = $request->input('other_payment_code', []);
            $descriptions = $request->input('other_payment_description', []);
            $amounts = $request->input('other_payment_amount', []);
            $subsidyAmounts = $request->input('employment_subsidy_amount', []);

            foreach ($types as $index => $type) {
                if (!empty($type) && !empty($codes[$index])) {
                    $otherPayments[] = [
                        'other_payment_type' => $type,
                        'code' => $codes[$index],
                        'description' => $descriptions[$index] ?? '',
                        'amount' => (float)($amounts[$index] ?? 0),
                        'employment_subsidy_amount' => (float)($subsidyAmounts[$index] ?? 0),
                    ];
                }
            }
        }

        // Desde datos JSON
        if (empty($otherPayments) && isset($payrollData['Complemento']['Payroll']['OtherPayments'])) {
            foreach ($payrollData['Complemento']['Payroll']['OtherPayments'] as $otherPayment) {
                $otherPayments[] = [
                    'other_payment_type' => $otherPayment['OtherPaymentType'],
                    'code' => $otherPayment['Code'],
                    'description' => $otherPayment['Description'],
                    'amount' => (float) ($otherPayment['Amount'] ?? 0),
                    'employment_subsidy_amount' => isset($otherPayment['EmploymentSubsidy'])
                        ? (float) $otherPayment['EmploymentSubsidy']['Amount']
                        : 0,
                ];
            }
        }

        // Guardar en base de datos
        foreach ($otherPayments as $otherPaymentData) {
            $payroll->otherPayments()->create($otherPaymentData);
        }
    }
}