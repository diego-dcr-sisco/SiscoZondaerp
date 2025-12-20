<?php
namespace App\Http\Controllers;

use App\Enums\CfdiUsage as EnumCfdiUsage;
use App\Enums\PaymentForm as EnumPaymentForm;
use App\Enums\PaymentMethod;
use App\Enums\PositionRisks;
use App\Enums\TaxpayerType;
use App\Enums\TaxRegime as EnumTaxRegime;
use App\Models\ApplicationArea;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\CompanyCategory;
use App\Models\ControlPoint;
use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\CustomerReference;
use App\Models\Device;
use App\Models\DevicePest;
use App\Models\Filenames;
use App\Models\FloorPlans;
use App\Models\FloorplanVersion;
use App\Models\Floortype;
use App\Models\InvoiceCustomer;
use App\Models\Lead;
use App\Models\OrderIncidents;
use App\Models\PestCatalog;
use App\Models\Reference_type;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\TaxRegime;
use App\Models\User;
use App\Models\UserCustomer;
use App\Models\ZoneType;
use Carbon\Carbon;
use File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

// Cache

class CustomerController extends Controller
{
    // Constants for graphics
    private const STATUS_APPROVED = 5;
    private const QUESTION_CONSUMPTION = 13;

    private $files_path = 'customers/files/';
    private $cities_route = 'datas/json/Mexico_cities.json';
    private $states_route = 'datas/json/Mexico_states.json';
    private $size = 50;

    private $contact_medium = [
        'whatsapp' => 'WhatsApp',
        'sms' => 'Mensaje SMS',
        'call' => 'Llamada telefónica',
        'email' => 'Correo electrónico',
        'flyer' => 'Volanteo físico',
    ];

    private $creditTimes;

    private $paymentForms;

    private $paymentMethods;

    private $navigation;

    private $navigation_invoices;

    private $taxRegimes;

    private $cfdiUsages;

    private $consumption_value;

    private $graphs_types;

    public function __construct()
    {
        $this->navigation_invoices = [
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

        // Mapeo de respuestas -> valor decimal
        $this->consumption_value = [
            'nulo' => 0,
            'bajo' => 0.25,
            'medio' => 0.5,
            'alto' => 0.75,
            'consumototal' => 1,
        ];

        $this->graphs_types = [
            'cnsm' => 'Consumos',
            'cptr' => 'Capturas'
        ];

        $this->creditTimes = [
            '30' => '30 días',
            '60' => '60 días',
            '90' => '90 días',
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
            '99' => 'Por definir',
        ];

        $this->paymentMethods = [
            'PUE' => 'Pago en una sola exhibición',
            'PPD' => 'Pago en parcialidades ó diferido',
        ];

        $this->taxRegimes = [
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "General de Ley Personas Morales",
                "Value" => "601",
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Personas Morales con Fines no Lucrativos",
                "Value" => "603",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Sueldos y Salarios e Ingresos Asimilados a Salarios",
                "Value" => "605",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Arrendamiento",
                "Value" => "606",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de EnajenaciregimenesFiscales =ón o Adquisición de Bienes",
                "Value" => "607",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Demás ingresos",
                "Value" => "608",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Residentes en el Extranjero sin Establecimiento Permanente en México",
                "Value" => "610",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Ingresos por Dividendos (socios y accionistas)",
                "Value" => "611",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Personas Físicas con Actividades Empresariales y Profesionales",
                "Value" => "612",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Ingresos por intereses",
                "Value" => "614",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de los ingresos por obtención de premios",
                "Value" => "615",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Sin obligaciones fiscales",
                "Value" => "616",
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Sociedades Cooperativas de Producción que optan por diferir sus ingresos",
                "Value" => "620",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Incorporación Fiscal",
                "Value" => "621",
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras",
                "Value" => "622",
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Opcional para Grupos de Sociedades",
                "Value" => "623",
            ],
            [
                "Natural" => false,
                "Moral" => true,
                "Name" => "Coordinados",
                "Value" => "624",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas",
                "Value" => "625",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Régimen Simplificado de Confianza",
                "Value" => "626",
            ],
        ];

        $this->cfdiUsages = [
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Nómina",
                "Value" => "CN01",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Pagos",
                "Value" => "CP01",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Honorarios médicos, dentales y gastos hospitalarios.",
                "Value" => "D01",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos médicos por incapacidad o discapacidad",
                "Value" => "D02",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos funerales.",
                "Value" => "D03",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Donativos.",
                "Value" => "D04",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).",
                "Value" => "D05",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Aportaciones voluntarias al SAR.",
                "Value" => "D06",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Primas por seguros de gastos médicos.",
                "Value" => "D07",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Gastos de transportación escolar obligatoria.",
                "Value" => "D08",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.",
                "Value" => "D09",
            ],
            [
                "Natural" => true,
                "Moral" => false,
                "Name" => "Pagos por servicios educativos (colegiaturas)",
                "Value" => "D10",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Adquisición de mercancias",
                "Value" => "G01",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Devoluciones, descuentos o bonificaciones",
                "Value" => "G02",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Gastos en general",
                "Value" => "G03",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Construcciones",
                "Value" => "I01",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Mobilario y equipo de oficina por inversiones",
                "Value" => "I02",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Equipo de transporte",
                "Value" => "I03",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Equipo de computo y accesorios",
                "Value" => "I04",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Dados, troqueles, moldes, matrices y herramental",
                "Value" => "I05",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Comunicaciones telefónicas",
                "Value" => "I06",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Comunicaciones satelitales",
                "Value" => "I07",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Otra maquinaria y equipo",
                "Value" => "I08",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Por Definir",
                "Value" => "P01",
            ],
            [
                "Natural" => true,
                "Moral" => true,
                "Name" => "Sin efectos fiscales",
                "Value" => "S01",
            ],
        ];
    }

    public function generateCustomerCode(string $name, int $length = 3, string $model = Customer::class): string
    {
        $name = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $prefix = substr($name, 0, rand(2, 3));
        $randomPart = Str::upper(Str::random($length));
        $code = $prefix . $randomPart;
        $originalCode = $code;
        $counter = 1;

        while ($model::where('code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }

        return $code;
    }

    public function index(): View
    {
        $customers = Customer::where('general_sedes', 0)->where('status', '!=', 0)->orderBy('name')->paginate($this->size);
        $service_types = ServiceType::all();

        $categories = [
            '1' => 'Clientes',
            '2' => 'Sedes',
            '3' => 'Clientes Potenciales',
        ];

        $navigation = [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
            'Facturacion' => route('invoices.index'),
        ];

        return view('customer.index.simple', compact('customers', 'service_types', 'categories', 'navigation'));
    }

    public function indexSedes(): View
    {
        $customers = Customer::where('general_sedes', '!=', 0)->where('status', '!=', 0)->orderBy('name')->paginate($this->size);
        $service_types = ServiceType::all();

        $categories = [
            '1' => 'Clientes',
            '2' => 'Sedes',
            '3' => 'Clientes Potenciales',
        ];

        $navigation = [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
            'Facturacion' => route('invoices.index'),
        ];

        return view('customer.index.sedes', compact('customers', 'service_types', 'categories', 'navigation'));
    }

    public function indexLeads(): View
    {
        $customers = Lead::orderBy('name')->paginate($this->size);
        $service_types = ServiceType::all();

        $categories = [
            '1' => 'Clientes',
            '2' => 'Sedes',
            '3' => 'Clientes Potenciales',
        ];

        $navigation = [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
            'Facturacion' => route('invoices.index'),
        ];

        return view('customer.index.leads', compact('customers', 'service_types', 'navigation', 'categories'));
    }

    public function create(): View
    {
        $companies = Company::all();
        $categories = CompanyCategory::all();
        $branches = Branch::all();
        $service_types = ServiceType::all();
        $contact_medium = $this->contact_medium;
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        return view('customer.create.simple')->with(compact('companies', 'branches', 'categories', 'service_types', 'states', 'cities', 'contact_medium'));
    }

    public function createSede(int $matrix): View
    {
        $customer_matrix = $matrix ? Customer::find(id: $matrix) : null;
        $customers = Customer::where('general_sedes', 0)->orderBy('name')->get();
        $companies = Company::all();
        $categories = CompanyCategory::all();
        $branches = Branch::all();
        $service_types = ServiceType::all();
        $contact_medium = $this->contact_medium;
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        session()->flash('warning', 'Se sugiere renombrar la nueva sede para prevenir cualquier posible confusión con la matriz.');

        return view('customer.create.sede')->with(
            compact('companies', 'branches', 'categories', 'service_types', 'states', 'cities', 'contact_medium', 'customer_matrix', 'customers')
        );
    }

    public function createLead(): View
    {
        $companies = Company::all();
        $categories = CompanyCategory::all();
        $branches = Branch::all();
        $service_types = ServiceType::all();
        $contact_medium = $this->contact_medium;
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        return view('customer.create.lead')->with(compact('companies', 'branches', 'categories', 'service_types', 'states', 'cities', 'contact_medium'));
    }

    public function storeLead(Request $request)
    {
        $customer = new Lead();
        $customer->fill($request->all());
        $customer->status = 1;
        $customer->save();

        return redirect()->route('customer.index.leads');
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = new Customer();
        $customer->blueprints = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->print_doc = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->validate_certificate = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->code = $this->generateCustomerCode($request->name);

        $customer->fill($request->all());
        $customer->status = 1;
        $customer->save();

        // Handle file attachments and properties
        if ($customer->service_type_id != 1) {
            $files = Filenames::where('type', 'customer')->get();
            foreach ($files as $file) {
                CustomerFile::insert([
                    'customer_id' => $customer->id,
                    'filename_id' => $file->id,
                ]);
            }
        }

        // Handle properties for type 1 customers
        /*if ($customer->service_type_id != 1 && $customer->general_sedes == 0) {
            // Create sede
            $sede = new Customer();
            $sede->fill($request->all());
            $sede->name = $request->name . ' ' . $request->city;
            $sede->general_sedes = $customer->id;
            $sede->status = 1;
            $sede->code = $this->generateCustomerCode($request->name);
            $sede->save();

            foreach ($files as $file) {
                CustomerFile::insert([
                    'customer_id' => $sede->id,
                    'filename_id' => $file->id,
                ]);
            }

            $propsDefault = [2, 3, 4, 5];
            foreach ($propsDefault as $prop) {
                CustomerProperties::insert([
                    'customer_id' => $sede->id,
                    'property_id' => $prop,
                ]);
            }
        }*/
        return redirect()->route('customer.index');
    }

    public function storeSede(Request $request)
    {
        $customer = new Customer();
        $customer->blueprints = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->print_doc = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->validate_certificate = $request->input('service_type_id') == 3 ? 1 : 0;
        $customer->code = $this->generateCustomerCode($request->name);

        $customer->fill($request->all());
        $customer->status = 1;
        $customer->general_sedes = $request->input('customer_matrix') ?: 0;
        $customer->save();

        return redirect()->route('customer.index.sedes');
    }

    public function uploadFile(Request $request, string $customerId)
    {
        $request->validate([
            'file' => 'required|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $file = $request->file('file');

        $disk = Storage::disk('public');
        $customer = Customer::find($customerId);

        $customer_file = CustomerFile::updateOrCreate([
            'customer_id' => $customer->id,
            'filename_id' => $request->filename_id,
        ], [
            'expirated_at' => $request->expirated_at,
            'updated_at' => now(),
        ]);

        if ($customer_file->path && $disk->exists($customer_file->path)) {
            $disk->delete($customer_file->path);
        }

        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $newFileName = $fileName . '_' . time() . '.' . $extension;

        $filePath = $this->files_path . $newFileName;

        $disk->put($filePath, file_get_contents($file));

        $customer_file->update(['path' => $filePath]);

        return back();
    }

    public function showCustomerDetails(string $id): View
    {
        $client = $floortype = $prope = $actibableprop = $actibableprop = $defaultinac = $defaultprop = $activeprop = $sedes = $reference_types = $refs = $customer_file = $zones = $floorplans = null;

        $companies = Company::all();
        $companyCategories = CompanyCategory::all();
        $services = ServiceType::all();
        $branches = Branch::all();
        $tax_regimes = TaxRegime::all();
        $referenceTypes = Reference_type::all();
        $floorTypes = FloorType::all();

        $customer = Customer::find($id);

        $products = 0;
        $pendingCount = 0;
        $customerPending = [];

        foreach ($customer->floorplans as $floorplan) {
            foreach ($floorplan->devices($floorplan->versions->pluck('version')->first())->get() as $device) {
                $products++;
            }
        }

        foreach ($customer->contracts as $contract) {
            $endDate = Carbon::parse($contract->enddate);

            if ($endDate->isBetween(Carbon::now(), Carbon::now()->addDays(31))) {
                $pendingCount++;

                $customerPending[$pendingCount] = [
                    'id' => $contract->id,
                    'content' => 'El contrato con id "' . $contract->id . '" esta apunto de expirar.',
                    'date' => $contract->enddate,
                    'type' => 'contract',
                ];
            }
        }

        foreach ($customer->ordersPending as $order) {
            $programmed_date = Carbon::parse($order->programmed_date);
            if ($programmed_date->isBetween(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), true)) {
                $pendingCount++;
                $servicesNames = [];
                foreach ($order->services as $service) {
                    $servicesNames[] = $service->name;
                }

                $customerPending[$pendingCount] = [
                    'id' => $order->id,
                    'content' => 'La orden de servicio con id "' . $order->id . '" con los servicios "' . implode(', ', $servicesNames) . '", esta programada para esta semana.',
                    'date' => $order->programmed_date,
                    'type' => 'order',
                ];
            }
        }

        foreach ($customer->files as $file) {
            $expirated_date = Carbon::parse($file->expirated_at);
            if ($expirated_date->isBetween(Carbon::now(), Carbon::now()->addDays(31), true)) {
                $pendingCount++;

                $customerPending[$pendingCount] = [
                    'id' => $file->id,
                    'content' => 'El Documento "' . $file->filename->name . '" esta apunto de expirar.',
                    'date' => $file->expirated_at,
                    'type' => 'file',
                ];
            }
        }

        $customerData = [
            'servicePendiente' => $customer->countOrdersbyStatus(1),
            'serviceAccepted' => $customer->countOrdersbyStatus(2),
            'serviceFinished' => $customer->countOrdersbyStatus(3),
            'serviceVerified' => $customer->countOrdersByStatus(4),
            'serviceApproved' => $customer->countOrdersByStatus(5),
            'serviceCanceled' => $customer->countOrdersByStatus(6),
            'floorplansCount' => $customer->floorplans->count(),
            'applicationAreaCount' => $customer->applicationAreas()->count(),
            'devices' => $products,
            'customerFile' => $customer->files->where('path', '!=', null)->count(),
            'pendings' => $customerPending,
        ];

        $states = file_get_contents(public_path($this->states_route));
        $cities = file_get_contents(public_path($this->cities_route));
        $states = json_decode($states, true);
        $cities = json_decode($cities, true);

        return view('customer.show.details', compact('customer', 'customerData', 'companies', 'companyCategories', 'services', 'branches', 'tax_regimes', 'referenceTypes', 'floorTypes'));
    }

    public function createReference(string $id, string $type): View
    {
        $states = file_get_contents(public_path($this->states_route));
        $cities = file_get_contents(public_path($this->cities_route));
        $states = json_decode($states, true);
        $cities = json_decode($cities, true);
        $reference_types = Reference_type::all();
        return view('customer.create.reference', compact('reference_types', 'id', 'type', 'states', 'cities'));
    }

    public function editReference(string $id, string $type): View
    {
        $states = file_get_contents(public_path($this->states_route));
        $cities = file_get_contents(public_path($this->cities_route));
        $states = json_decode($states, true);
        $cities = json_decode($cities, true);
        $reference_types = Reference_type::all();
        $reference = CustomerReference::find($id);

        return view('customer.edit.reference.references', compact('reference', 'reference_types', 'id', 'type', 'states', 'cities'));
    }

    public function storeReference(Request $request, string $customerId)
    {
        $reference = new CustomerReference();
        $reference->fill($request->all());
        $reference->customer_id = $customerId;
        $reference->save();

        return back();
    }

    public function updateReference(Request $request, string $id)
    {
        $reference = CustomerReference::find($id); // Obtener la referencia por su ID
        $reference->fill($request->all());
        $reference->save();
        return back();
    }

    public function destroyReference(string $id)
    {
        try {
            $reference = CustomerReference::findOrFail($id);
            $reference->delete();
            return back();
        } catch (\Exception $e) {
            return back();
        }
    }

    public function storeArea(Request $request, string $customerId)
    {
        $request->validate([
            'm2' => 'required|numeric|min:0|max:10000',
        ]);

        $area = new ApplicationArea();
        $area->fill($request->all());
        $area->customer_id = $customerId;
        $area->save();

        return back();
    }

    public function show(string $id, int $type, int $section): View
    {
        $client = $floortype = $prope = $actibableprop = $actibableprop = $defaultinac = $defaultprop = $activeprop = $sedes = $reference_types = $refs = $customer_file = $zones = $floorplans = null;

        $companies = Company::all();
        $companyCategories = CompanyCategory::all();
        $services = ServiceType::all();
        $branches = Branch::all();
        $tax_regimes = TaxRegime::all();
        $referenceTypes = Reference_type::all();
        $floorTypes = FloorType::all();

        $customer = $type != 0 ? Customer::find($id) : Lead::find($id);

        $states = file_get_contents(public_path($this->states_route));
        $cities = file_get_contents(public_path($this->cities_route));
        $states = json_decode($states, true);
        $cities = json_decode($cities, true);

        return view('customer.show', compact('customer', 'companies', 'companyCategories', 'services', 'branches', 'tax_regimes', 'referenceTypes', 'floorTypes', 'type', 'section'));
    }

    public function showSede(int $matrix)
    {
        $customer = Customer::find($matrix);
        if (!$customer) {
            return back()->with('error', 'Cliente no encontrado');
        }

        $sedes = Customer::where('general_sedes', $matrix)->get();
        $service_types = ServiceType::all();

        $navigation = $customer->service_type_id == 1 ?
            [
                'Cliente' => route('customer.edit', ['id' => $customer->id]),
            ] :
            [
                'Cliente' => route('customer.edit', ['id' => $customer->id]),
                'Sedes' => route('customer.show.sede', ['matrix' => $customer->id]),
            ];

        return view('customer.show.sede', compact('customer', 'sedes', 'navigation', 'service_types'));
    }

    public function showSedeFiles(string $id)
    {
        $filenames = Filenames::where('type', 'customer')->get();
        $customer = Customer::find($id);
        $service_types = ServiceType::all();
        $navigation = [
            'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
            'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
            'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
            'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
            //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            'Graficas' => route('customer.graphics', ['id' => $customer->id]),
        ];
        return view('customer.show.files', compact('customer', 'filenames', 'navigation', 'service_types'));
    }

    public function showSedeFloorplans(string $id)
    {
        $customer = Customer::find($id);
        $service_types = ServiceType::all();
        $services = Service::orderBy('name')->get();
        $navigation = [
            'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
            'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
            'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
            'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
            //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            'Graficas' => route('customer.graphics', ['id' => $customer->id]),
        ];
        return view('customer.show.floorplans', compact('customer', 'navigation', 'service_types', 'services'));
    }

    public function showSedePortal(string $id)
    {
        $customer = Customer::find($id);
        $service_types = ServiceType::all();
        $services = Service::all();

        $access = User::whereIn('id', UserCustomer::where('customer_id', $customer->id)->pluck('user_id'))
            ->get();

        $navigation = [
            'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
            'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
            'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
            'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
            //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            'Graficas' => route('customer.graphics', ['id' => $customer->id]),
        ];
        return view('customer.show.portal', compact('customer', 'navigation', 'service_types', 'services', 'access'));
    }
    public function showSedeAreas(string $id)
    {
        $zone_types = ZoneType::all();
        $customer = Customer::with('applicationAreas')->find($id);
        $service_types = ServiceType::all();
        $navigation = [
            'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
            'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
            'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
            'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
            //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            'Graficas' => route('customer.graphics', ['id' => $customer->id]),
        ];

        return view('customer.show.areas', compact('customer', 'zone_types', 'navigation', 'service_types'));
    }

    public function edit(string $id)
    {
        $contact_medium = $this->contact_medium;
        $tax_regimes = TaxRegime::all();
        $categories = CompanyCategory::all();
        $service_types = ServiceType::all();
        $branches = Branch::all();
        $floortype = Floortype::all();
        $reference_types = Reference_type::all();
        $zone_types = ZoneType::all();
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        $customer = Customer::find($id);

        $navigation = $customer->service_type_id == 1 ?
            [
                'Cliente' => route('customer.edit', ['id' => $customer->id]),
            ] :
            [
                'Cliente' => route('customer.edit', ['id' => $customer->id]),
                'Sedes' => route('customer.show.sede', ['matrix' => $customer->id]),
            ];

        return view(
            'customer.edit.forms.simple',
            compact(
                'zone_types',
                'tax_regimes',
                'customer',
                'navigation',
                'branches',
                'categories',
                'reference_types',
                'service_types',
                'states',
                'cities',
                'contact_medium'
            )
        );
    }

    public function editSede(string $id)
    {
        $contact_medium = $this->contact_medium;
        $tax_regimes = TaxRegime::all();
        $categories = CompanyCategory::all();
        $service_types = ServiceType::all();
        $branches = Branch::all();
        $floortype = Floortype::all();
        $reference_types = Reference_type::all();
        $zone_types = ZoneType::all();
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        $customer = Customer::find($id);

        $navigation = [
            'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
            'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
            'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
            'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
            //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
            'Graficas' => route('customer.graphics', ['id' => $customer->id]),
        ];

        return view(
            'customer.edit.forms.sede',
            compact(
                'zone_types',
                'tax_regimes',
                'customer',
                'branches',
                'categories',
                'reference_types',
                'service_types',
                'states',
                'cities',
                'contact_medium',
                'navigation'
            )
        );
    }

    public function editLead(string $id)
    {
        $contact_medium = $this->contact_medium;
        $tax_regimes = TaxRegime::all();
        $categories = CompanyCategory::all();
        $service_types = ServiceType::all();
        $branches = Branch::all();
        $floortype = Floortype::all();
        $reference_types = Reference_type::all();
        $zone_types = ZoneType::all();
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        $lead = Lead::find($id);

        $navigation = [
            'Cliente potencial' => route('customer.edit.lead', ['id' => $lead->id]),
            'Cotizaciones' => route('customer.quote', ['id' => $lead->id, 'class' => 'lead']),
        ];

        return view(
            'customer.edit.forms.lead',
            compact(
                'zone_types',
                'tax_regimes',
                'lead',
                'branches',
                'categories',
                'reference_types',
                'service_types',
                'states',
                'cities',
                'contact_medium',
                'navigation'
            )
        );
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return back();
        }
        $customer->fill($request->all());
        $customer->save();
        return back()->with('success', 'Cliente actualizado correctamente');
    }

    public function updateLead(Request $request, string $id): RedirectResponse
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return back();
        }
        $lead->fill($request->all());
        $lead->save();
        return back()->with('success', 'Cliente actualizado correctamente');
    }

    public function updateArea(Request $request, string $id)
    {
        $area = ApplicationArea::find($id);
        $area->fill($request->all());
        $area->save();
        return back();
    }

    public function search(Request $request)
    {
        //dd($request->all());
        $type = $request->input('category', 1); // Default to type 1 (Cliente)
        $query = $type == 3 ? Lead::query() : Customer::query();

        // Aplicar condición de estado
        $query->where('status', '!=', 0);

        // Aplicar condición de tipo (matriz/sedes)
        if ($type == 1) {
            $query->where('general_sedes', 0);
        } elseif ($type == 2) {
            $query->where('general_sedes', '!=', 0);
        }

        // Búsqueda general
        if ($request->filled('name')) {
            $searchTerm = '%' . $request->name . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm);
                //->orWhere('email', 'LIKE', $searchTerm)
                //->orWhere('phone', 'LIKE', $searchTerm);
            });
        }

        if ($request->filled('code')) {
            $searchTerm = '%' . $request->code . '%';
            $query->where('code', 'LIKE', $searchTerm);
        }

        // Filtro por tipo de servicio (solo para clientes)
        if ($request->filled('service_type')) {
            $query->where('service_type_id', $request->service_type);
        }

        // Filtro por rango de fechas
        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($date) {
                return Carbon::createFromFormat('d/m/Y', trim($date));
            }, explode(' - ', $request->date_range));

            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay(),
            ]);
        }

        // Ordenar y paginar
        $customers = $query->orderBy('name', 'desc')
            ->paginate($this->size)
            ->appends($request->query());

        $service_types = ServiceType::all();
        $categories = [
            '1' => 'Clientes',
            '2' => 'Sedes',
            '3' => 'Clientes Potenciales',
        ];
        $navigation = [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
            'Facturacion' => route('invoices.index'),
        ];

        return view($type == 1 ? 'customer.index.simple' : ($type == 2 ? 'customer.index.sedes' : 'customer.index.leads'), compact('customers', 'service_types', 'categories', 'navigation'));
    }

    public function downloadFile($id)
    {

        try {
            $customer_file = CustomerFile::find($id);

            if (Storage::disk('public')->exists($customer_file->path)) {
                return response()->download(storage_path('app/public/' . $customer_file->path));
            }
            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while downloading the file.'], 500);
        }
    }

    public function destroy(string $id)
    {
        $customer = Customer::find($id);
        if ($customer->general_sedes == 0) {
            Customer::where('general_sedes', $customer->id)->delete();
        }
        $customer->delete();
        $message = 'Cliente' . $customer->id . ' [' . $customer->name . '] eliminado';
        session()->flash('success', $message);
        return back();
    }

    public function destroyLead(string $id)
    {
        $lead = Lead::find($id);
        $lead->delete();
        $message = 'Cliente' . $lead->id . ' [' . $lead->name . '] eliminado';
        session()->flash('success', $message);
        return back();
    }

    public function destroyFile(string $id)
    {
        $file = CustomerFile::findOrFail($id);
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();
        return back()->with('success', 'Archivo eliminado correctamente');
    }

    public function destroyArea(string $id)
    {
        ApplicationArea::find($id)->delete();
        return back();
    }

    public function convert(int $id)
    {
        $lead = Lead::find($id);
        if ($lead) {
            $data = $lead->toArray();
            unset($data['id']);
            unset($data['reason']);
            unset($data['tracking_at']);

            // Create customer but prevent observer from firing
            $customer = new Customer($data);
            $customer->general_sedes = 0;
            $customer->code = $this->generateCustomerCode($customer->name);
            Customer::withoutEvents(function () use ($customer) {
                $customer->save();
            });

            // Delete the lead
            $lead->delete();
        }
        return redirect()->route('customer.index', ['type' => 1, 'page' => 1]);
    }

    public function tracking(int $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->tracking_at = Carbon::now()->toDateString();
        $lead->save();
        return back();
    }

    /////////////////////////////////////////////////////////////////////////////////
    // FUNCIONES PARA MODULO DE FACTURACION

    public function getInvoiceCustomers(Request $request)
    {
        $navigation = $this->navigation_invoices;
        $taxpayer_types = TaxpayerType::cases();
        $invoice_customers = InvoiceCustomer::all();

        return view('invoices.clients.index', compact(
            'navigation',
            'invoice_customers'
        ));
    }

    public function createInvoiceCustomer()
    {
        $navigation = $this->navigation_invoices;

        $creditTimes = $this->creditTimes;
        $paymentMethods = PaymentMethod::cases();
        $paymentForms = EnumPaymentForm::cases();
        $taxRegimes = EnumTaxRegime::cases();
        $cfdiUsages = EnumCfdiUsage::cases();
        $taxpayer_types = TaxpayerType::cases();
        $position_risks = PositionRisks::cases();

        return view('invoices.clients.create', compact(
            'creditTimes',
            'paymentMethods',
            'paymentForms',
            'taxRegimes',
            'cfdiUsages',
            'position_risks',
            'taxpayer_types',
        ));
    }

    public function storeInvoiceCustomer(Request $request)
    {
        $is_worker = $request->type == 'worker';

        $data = [
            'taxpayer' => $request->taxpayer,
            'type' => $request->type,
            'name' => $is_worker ? $request->worker_name : $request->customer_name,
            'rfc' => $is_worker ? $request->worker_rfc : $request->customer_rfc,
            'social_reason' => $request->customer_social_reason,
            'phone' => $request->phone,
            'email' => $request->email,
            'tax_system' => $is_worker ? $request->worker_tax_system : $request->customer_tax_system,
            'cfdi_usage' => $is_worker ? $request->worker_cfdi_usage : $request->customer_cfdi_usage,
            'zip_code' => $request->zip_code,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit,
            'credit_days' => $request->credit_days,
            'payment_method' => $request->payment_method,
            'payment_form' => $request->payment_form,
            'status' => $request->status,
        ];

        // Agregar campos específicos de trabajador
        if ($is_worker) {
            $data['curp'] = $request->worker_curp;
            $data['nss'] = $request->worker_nss;
            $data['salary_daily'] = $request->worker_salary_daily;
            $data['position_risk'] = $request->worker_position_risk;
            $data['department'] = $request->worker_department;
            $data['position'] = $request->worker_position;
        }

        InvoiceCustomer::create($data);

        return back()->with('success', 'Registro creado exitosamente');
    }

    public function editInvoiceCustomer(string $id)
    {
        $taxpayer = InvoiceCustomer::find($id);

        $creditTimes = $this->creditTimes;
        $paymentMethods = PaymentMethod::cases();
        $paymentForms = EnumPaymentForm::cases();
        $taxRegimes = EnumTaxRegime::cases();
        $cfdiUsages = EnumCfdiUsage::cases();
        $taxpayer_types = TaxpayerType::cases();
        $position_risks = PositionRisks::cases();

        return view('invoices.clients.edit', compact(
            'taxpayer',
            'creditTimes',
            'paymentMethods',
            'paymentForms',
            'taxRegimes',
            'cfdiUsages',
            'position_risks',
            'taxpayer_types',
        ));
    }

    public function updateInvoiceCustomer(Request $request, string $id)
    {
        try {
            $is_worker = $request->type == 'worker';

            $data = [
                'taxpayer' => $request->taxpayer,
                'type' => $request->type,
                'name' => $is_worker ? $request->worker_name : $request->customer_name,
                'rfc' => $is_worker ? $request->worker_rfc : $request->customer_rfc,
                'social_reason' => $request->customer_social_reason,
                'phone' => $request->phone,
                'email' => $request->email,
                'tax_system' => $is_worker ? $request->worker_tax_system : $request->customer_tax_system,
                'cfdi_usage' => $is_worker ? $request->worker_cfdi_usage : $request->customer_cfdi_usage,
                'zip_code' => $request->zip_code,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'credit_limit' => $request->credit_limit,
                'credit_days' => $request->credit_days,
                'payment_method' => $request->payment_method,
                'payment_form' => $request->payment_form,
                'status' => $request->status,
            ];

            // Agregar campos específicos de trabajador
            if ($is_worker) {
                $data['curp'] = $request->worker_curp;
                $data['nss'] = $request->worker_nss;
                $data['salary_daily'] = $request->worker_salary_daily;
                $data['position_risk'] = $request->worker_position_risk;
                $data['department'] = $request->worker_department;
                $data['position'] = $request->worker_position;
            }

            $taxpayer = InvoiceCustomer::find($id);
            $taxpayer->update($data);
            return back()->with('success', 'Registro actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el registro: ' . $e->getMessage());
        }
    }

    public function destroyTaxCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('invoices.customers')->with('success', 'Cliente eliminado correctamente');
    }

    private function hasRequiredFields(array $data): bool
    {
        // Filtrar campos con valor real
        $filled = array_filter($data, fn($value) => !is_null($value) && $value !== '');

        // Verificar que existan los 2 campos obligatorios (customer viene de ruta)
        return isset($filled['date_range'])
            && isset($filled['graph_type']);
    }

    /**
     * Helper method to return graphics view with consistent structure
     */
    private function returnGraphicsView($customer, $app_areas, $request_data, $messageType = null, $message = null)
    {
        $view = view('customer.show.graphics', [
            'customer' => $customer,
            'pests_headers' => [],
            'data' => ['detections' => [], 'headers' => []],
            'control_points' => collect(),
            'app_areas' => $app_areas,
            'graphs_types' => $this->graphs_types,
            'request_data' => $request_data,
        ]);

        if ($messageType && $message) {
            $view->with($messageType, $message);
        }

        return $view;
    }

    public function showGraphics(Request $request, string $id)
    {
        // Validation: customer ID comes from route parameter, not request
        $request->validate([
            'date_range' => 'nullable|string',
            'graph_type' => 'nullable|in:cnsm,cptr',
            'area' => 'nullable|string|max:255',
            'pest' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
        ], [
            'date_range.string' => 'El rango de fechas debe ser texto',
            'graph_type.in' => 'Tipo de gráfico no válido',
        ]);

        // Inicializar variables
        $data = [
            'detections' => [],
            'headers' => []
        ];


        $req_areas = [];
        $req_pests = [];
        $app_areas = [];
        $pests_headers = [];
        $control_points = [];

        // Cargar relaciones específicas con select para minimizar datos
        $customer = Customer::with([
            'applicationAreas' => function ($query) {
                $query->select('id', 'name', 'customer_id');
            },
            'applicationAreas.devices' => function ($query) {
                $query->select('id', 'code', 'version', 'application_area_id', 'floorplan_id');
            },
            'applicationAreas.devices.floorplan.service' => function ($query) {
                $query->select('id', 'name');
            }
        ])->find($id);

        if (!$customer) {
            return $this->returnGraphicsView(null, [], $request->all(), 'error', 'Cliente no encontrado');
        }

        // Obtener áreas de aplicación sin consulta adicional
        $app_areas = $customer->applicationAreas;

        // Si no hay filtros aplicados, solo mostrar la vista con el formulario vacío
        if (!$this->hasRequiredFields($request->all())) {
            return $this->returnGraphicsView($customer, $app_areas, $request->all());
        }

        // Aplica filtros usando like solo si es necesario
        if ($request->filled('area')) {
            $req_areas = ApplicationArea::where('customer_id', $customer->id)
                ->where('name', 'like', '%' . $request->input('area') . '%')
                ->pluck('id')
                ->toArray();
        }

        if ($request->filled('pest')) {
            $req_pests = PestCatalog::where('name', 'like', '%' . $request->input('pest') . '%')
                ->pluck('id')
                ->toArray();
        }

        // Optimizar consulta de órdenes
        $order_query = Order::where('customer_id', $customer->id)->where('status_id', self::STATUS_APPROVED);
        
        // Inicializar fechas
        $startDate = null;
        $endDate = null;

        if ($request->filled('date_range')) {
            try {
                [$startDate, $endDate] = array_map(function ($d) {
                    return Carbon::createFromFormat('d/m/Y', trim($d));
                }, explode(' - ', $request->input('date_range')));

                $order_query->whereBetween('programmed_date', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                ]);
            } catch (\Exception $e) {
                return $this->returnGraphicsView($customer, $app_areas, $request->all(), 'error', 'Formato de fecha inválido');
            }
        }

        if ($request->filled('service')) {
            $found_services = Service::where('name', 'like', '%' . $request->input('service') . '%')
                ->pluck('id');
            $order_ids = OrderService::whereIn('service_id', $found_services)
                ->pluck('order_id');
            $order_query->whereIn('id', $order_ids);
        }

        // Opción 1: Cargar servicios y luego asegurar unicidad
        $orders = $order_query->with(['services:service.id,service.name'])->get();

        // Si necesitas una colección única de todos los servicios
        /*$uniqueServices = $orders->flatMap->services->unique('id');

        // Opción 2: Obtener servicios únicos directamente desde la base de datos
        $services = Service::select('id', 'name')
            ->whereIn('id', function ($query) use ($order_query) {
                $query->select('service_id')
                    ->from('order_service')
                    ->whereIn('order_id', $order_query->pluck('id'));
            })
            ->distinct()
            ->get();*/



        if ($orders->isEmpty()) {
            return $this->returnGraphicsView($customer, $app_areas, $request->all(), 'info', 'No se encontraron órdenes aprobadas con los filtros aplicados');
        }
        

        // Cache orderIds to avoid repeated pluck() calls
        $orderIds = $orders->pluck('id')->toArray();

        // Use DISTINCT in SQL instead of unique() in PHP for better performance
        $device_ids = OrderIncidents::whereIn('order_id', $orderIds)
            ->distinct()
            ->pluck('device_id')
            ->toArray();

        // Optimizar consulta de dispositivos y control points
        $devices = Device::whereIn('id', $device_ids)
            ->with([
                'floorplan.service:id,name',
                'controlPoint:id,name'
            ])
            ->select('id', 'code', 'version', 'application_area_id', 'floorplan_id', 'type_control_point_id', 'nplan')
            ->get();

        if ($devices->isEmpty()) {
            return $this->returnGraphicsView($customer, $app_areas, $request->all(), 'error', 'No se encontraron dispositivos');
        }

        $control_points = ControlPoint::whereIn('id', $devices->pluck('type_control_point_id')->unique()->toArray())->get();

        $graph_type = $request->graph_type;

        // Pre-calculate devicesByArea once for reuse
        $devicesByArea = $devices->groupBy('application_area_id');

        // Obtener datos según el tipo de gráfico
        if ($graph_type == 'cnsm') {
            $data = $this->getGraphicDataWithDevicesByAnswer($customer, $orderIds, $orders, $devices, $devicesByArea, $startDate, $endDate);
        } else if ($graph_type == 'cptr') {
            $data = $this->getGraphicDataWithDevicesByPests($customer, $orderIds, $devices, $req_pests, $devicesByArea);
        } else {
            return $this->returnGraphicsView($customer, $app_areas, $request->all(), 'error', 'Tipo de gráfico no válido');
        }

        return view('customer.show.graphics', [
            'customer' => $customer,
            'pests_headers' => $data['headers'] ?? [],
            'data' => $data,
            'control_points' => $control_points,
            'app_areas' => $app_areas,
            'graphs_types' => $this->graphs_types,
            'request_data' => $request->all(),
        ])->with('success', 'Resultados encontrados');
    }

    private function getGraphicDataWithDevicesByPests($customer, $orderIds, $devices, $pests, $devicesByArea)
    {
        // Optimizar consulta usando joins y selects específicos
        $allDevicePests = DevicePest::whereIn('order_id', $orderIds)
            ->whereIn('device_id', $devices->pluck('id'))
            ->when(!empty($pests), function ($query) use ($pests) {
                $query->whereIn('pest_id', $pests);
            })
            ->with([
                'pest:id,name',
                'device:id,code,version,application_area_id,floorplan_id',
                'device.applicationArea:id,name'
            ])
            ->select('id', 'device_id', 'order_id', 'pest_id', 'total')
            ->get();

        $pests_headers = $allDevicePests->pluck('pest.name')
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Precalcular agrupaciones
        $devicePestsByDevice = $allDevicePests->groupBy('device_id');

        $data = [];

        foreach ($customer->applicationAreas as $area) {
            $areaDevices = $devicesByArea->get($area->id, collect());

            foreach ($areaDevices as $device) {
                $dpests = $devicePestsByDevice->get($device->id, collect());

                if ($dpests->isEmpty()) {
                    continue;
                }

                $pest_totals = array_fill_keys($pests_headers, 0);
                $total_detections = 0;

                // Sumar en un solo paso
                foreach ($dpests as $dpest) {
                    if ($dpest->pest && $dpest->pest->name) {
                        $pest_name = $dpest->pest->name;
                        $pest_totals[$pest_name] = ($pest_totals[$pest_name] ?? 0) + $dpest->total;
                        $total_detections += $dpest->total;
                    }
                }

                $data[] = [
                    'area_id' => $area->id,
                    'area_name' => $area->name,
                    'device_id' => $device->id,
                    'device_name' => $device->code,
                    'service' => $device->floorplan?->service?->name ?? 'N/A',
                    'versions' => [$device->version],
                    'pest_total_detections' => $pest_totals,
                    'total_detections' => $total_detections
                ];
            }
        }

        // Calcular totales generales (suma de todos los dispositivos por cada plaga)
        $grand_totals = array_fill_keys($pests_headers, 0);
        foreach ($data as $row) {
            foreach ($row['pest_total_detections'] as $pest => $total) {
                $grand_totals[$pest] += $total;
            }
        }

        return [
            'detections' => $data,
            'headers' => $pests_headers,
            'grand_totals' => $grand_totals
        ];
    }

    private function normalizeString($string)
    {
        // Convierte a minúsculas y elimina espacios
        return strtolower(str_replace(' ', '', $string));
    }

    private function getGraphicDataWithDevicesByAnswer($customer, $orderIds, $orders, $devices, $devicesByArea, $startDate = null, $endDate = null)
    {
        $groupedData = [];
        
        // Calcular semanas del rango si hay fechas
        $weekHeaders = [];
        $weekRanges = [];
        
        if ($startDate && $endDate) {
            $current = $startDate->copy()->startOfWeek();
            $end = $endDate->copy()->endOfWeek();
            $weekNumber = 1;
            
            while ($current <= $end) {
                $weekStart = $current->copy();
                $weekEnd = $current->copy()->endOfWeek();
                
                // Ajustar si excede el rango
                if ($weekStart < $startDate) $weekStart = $startDate->copy();
                if ($weekEnd > $endDate) $weekEnd = $endDate->copy();
                
                $weekKey = "S{$weekNumber}";
                $weekLabel = $weekKey . ' (' . $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m') . ')';
                
                $weekHeaders[] = $weekLabel;
                $weekRanges[$weekKey] = [
                    'start' => $weekStart->format('Y-m-d'),
                    'end' => $weekEnd->format('Y-m-d'),
                    'label' => $weekLabel
                ];
                
                $current->addWeek();
                $weekNumber++;
            }
        }

        // CRITICAL: Pre-load ALL incidents at once to eliminate N+1 query problem
        $allIncidents = OrderIncidents::whereIn('order_id', $orderIds)
            ->where('question_id', self::QUESTION_CONSUMPTION)
            ->whereIn('device_id', $devices->pluck('id'))
            ->select('id', 'device_id', 'order_id', 'answer')
            ->get();
        
        // Crear mapeo de order_id a programmed_date para evitar búsquedas repetidas
        $orderDates = $orders->pluck('programmed_date', 'id')->toArray();

        foreach ($customer->applicationAreas as $area) {
            $areaDevices = $devicesByArea->get($area->id, collect());

            if ($areaDevices->isEmpty()) {
                continue;
            }

            foreach ($areaDevices as $device) {
                // Get pre-loaded incidents for this device (no database query!)
                $incidents = $allIncidents->where('device_id', $device->id);

                $deviceTotalConsumption = 0;
                $deviceIncidentCount = $incidents->count();
                $weeklyConsumption = [];
                
                // Inicializar consumo semanal en 0
                foreach ($weekRanges as $weekKey => $range) {
                    $weeklyConsumption[$range['label']] = 0;
                }

                foreach ($incidents as $incident) {
                    $normalizedAnswer = $this->normalizeString($incident->answer);
                    $consumptionValue = $this->consumption_value[$normalizedAnswer] ?? 0;
                    $deviceTotalConsumption += $consumptionValue;
                    
                    // Asignar a la semana correspondiente
                    if (!empty($weekRanges) && isset($orderDates[$incident->order_id])) {
                        $orderDate = $orderDates[$incident->order_id];
                        
                        foreach ($weekRanges as $weekKey => $range) {
                            if ($orderDate >= $range['start'] && $orderDate <= $range['end']) {
                                $weeklyConsumption[$range['label']] += $consumptionValue;
                                break;
                            }
                        }
                    }
                }

                $key = $area->id . '_' . $device->nplan . '_' . $device->code;

                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'area_id' => $area->id,
                        'area_name' => $area->name,
                        'device_name' => $device->code,
                        'service' => $device->floorplan?->service?->name ?? 'N/A',
                        'nplan' => $device->nplan,
                        'device_count' => 1,
                        '_total_consumption' => $deviceTotalConsumption,
                        '_total_incidents' => $deviceIncidentCount,
                        '_weekly_consumption' => $weeklyConsumption,
                        'versions' => [$device->version],
                    ];
                } else {
                    $groupedData[$key]['device_count']++;
                    $groupedData[$key]['_total_consumption'] += $deviceTotalConsumption;
                    $groupedData[$key]['_total_incidents'] += $deviceIncidentCount;
                    
                    // Sumar consumos semanales
                    foreach ($weeklyConsumption as $weekLabel => $value) {
                        $groupedData[$key]['_weekly_consumption'][$weekLabel] += $value;
                    }

                    if (!in_array($device->version, $groupedData[$key]['versions'])) {
                        $groupedData[$key]['versions'][] = $device->version;
                    }
                }
            }
        }

        // Calcular el valor final de consumo para cada grupo
        $data = [];
        foreach ($groupedData as $key => $group) {
            if ($group['_total_incidents'] > 0) {
                $consumptionValue = $group['_total_consumption'];
            } else {
                $consumptionValue = 0;
            }

            $data[] = [
                'area_id' => $group['area_id'],
                'area_name' => $group['area_name'],
                'device_name' => $group['device_name'],
                'service' => $group['service'] ?? 'N/A',
                'nplan' => $group['nplan'],
                'device_count' => $group['device_count'],
                'versions' => $group['versions'],
                'consumption_value' => $consumptionValue,
                'weekly_consumption' => $group['_weekly_consumption'] ?? [],
            ];
        }

        // Calcular totales generales
        $grand_total_consumption = 0;
        $grand_totals_weekly = [];
        
        if (!empty($weekHeaders)) {
            // Inicializar totales semanales
            foreach ($weekHeaders as $weekLabel) {
                $grand_totals_weekly[$weekLabel] = 0;
            }
            
            // Sumar consumos de todos los dispositivos
            foreach ($data as $row) {
                foreach ($row['weekly_consumption'] as $weekLabel => $value) {
                    $grand_totals_weekly[$weekLabel] += $value;
                }
            }
        } else {
            // Sumar consumo total
            foreach ($data as $row) {
                $grand_total_consumption += $row['consumption_value'];
            }
        }

        return [
            'detections' => $data,
            'headers' => !empty($weekHeaders) ? $weekHeaders : ['Consumo Total'],
            'grand_total_consumption' => $grand_total_consumption,
            'grand_totals_weekly' => $grand_totals_weekly
        ];
    }
}