<?php
namespace App\Http\Controllers;

use App\Models\Administrative;
use App\Models\ApplicationMethod;
use App\Models\ApplicationMethodService;
use App\Models\Contract;
use App\Models\ControlPoint;
use App\Models\Customer;
use App\Models\DatabaseLog;
use App\Models\Device;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\OrderTechnician;
use App\Models\PestCategory;
use App\Models\PropagateService;
use App\Models\Service;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class OrderController extends Controller
{

    private $files_path = 'files/customers';
    private $file_answers_path = 'datas/json/answers.json';
    private $size = 50;

    private $navigation;

    private function generateDate($date, $number, $frequency)
    {
        $newDate = Carbon::createFromFormat('Y-m-d', $date);
        switch ($frequency) {
            case 1: // Día(s)
                $newDate->addDays($number);
                break;
            case 2: // Semana(s)
                $newDate->addWeeks($number);
                break;
            case 3: // Mes(es)
                $newDate->addMonths($number);
                break;
            case 4: // Año(s)
                $newDate->addYears($number);
                break;
            default:
                // Si la frequency no es válida, devolver la date actual
                return $date;
        }

        return $newDate->format('Y-m-d');
    }

    public function __construct()
    {
        $this->navigation = [
            'Ordenes de servicio' => route('order.index'),
            'Contratos' => route('contract.index'),
            'CRM' => route('crm.agenda'),
            'Facturas' => route('invoices.index'),
            'Seguimientos' => route('crm.tracking'),
        ];
    }

    public function index(): View
    {
        $orders = Order::join('customer', 'order.customer_id', '=', 'customer.id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED) ASC")
            ->orderBy('programmed_date')
            ->orderBy('customer.name', 'ASC')
            ->select('order.*')
            ->with('customer') // Para cargar la relación
            ->paginate($this->size);

        $order_status = OrderStatus::all();
        $size = $this->size;

        $customer_ranges = Customer::where('general_sedes', '!=', 0)->orWhere('service_type_id', 1)->orderBy('name', 'asc')->get();
        $navigation = $this->navigation;

        $technicians = Technician::with('user')
            ->whereIn('user_id', Technician::pluck('user_id'))
            ->join('user', 'technician.user_id', '=', 'user.id')
            ->orderBy('user.name', 'ASC')
            ->select('technician.*')
            ->get();

        return view(
            'order.index',
            compact(
                'orders',
                'order_status',
                'size',
                'customer_ranges',
                'technicians',
                'navigation'
            )
        );
    }

    public function create(): View
    {
        $success = $warning = $error = null;
        $pest_categories = PestCategory::orderBy('category', 'asc')->get();
        $application_methods = ApplicationMethod::orderBy('name', 'asc')->get();
        $technicians = Technician::with('user')
            ->whereIn('user_id', Technician::pluck('user_id'))
            ->join('user', 'technician.user_id', '=', 'user.id')
            ->orderBy('user.name', 'ASC')
            ->select('technician.*')
            ->get();
        $contracts = Contract::all();
        $order_status = OrderStatus::all();

        // Obtiene la URL almacenada
        $prevUrl = session()->get('prev_url') ?? url()->previous();

        // Guarda en sesión por si el usuario refresca la página
        session()->put('prev_url', $prevUrl);
        $view = 'order';

        return view(
            'order.create',
            with(
                compact(
                    'pest_categories',
                    'application_methods',
                    'technicians',
                    'contracts',
                    'order_status',
                    'prevUrl',
                    'view',
                )
            )
        );
    }

    public function store(Request $request)
    {
        $selected_services = json_decode($request->input('services'));
        $selected_technicians = json_decode($request->input('technicians'));

        if (!$request->input('customer_id')) {
            return back();
        }

        if (empty($selected_services)) {
            return back();
        }

        if (empty($selected_technicians)) {
            return back();
        }

        $customer = Customer::find($request->input('customer_id'));
        $count_orders = Order::where('customer_id', $customer->id)->count();

        $order = new Order();
        $order->administrative_id = Administrative::where('user_id', $user = Auth::user()->id)->first()->id;
        $order->customer_id = $customer->id;
        $order->start_time = $request->input('start_time');
        $order->end_time = $request->input('end_time');
        $order->programmed_date = $request->input('programmed_date');
        $order->status_id = 1;
        $order->contract_id = $request->input('contract_id') != 0 ? $request->input('contract_id') : null;
        $order->execution = $request->input('execution');
        $order->areas = $request->input('areas');
        $order->additional_comments = $request->input('additional_comments');
        $order->price = $request->input('price');
        $order->folio = $customer->code . '.' . ($order->contract_id ? ('MIP' . $order->contract_id) : 'SEG') . '-' . ++$count_orders;
        $order->created_at = now();
        $order->save();

        $order_technicians = [];

        foreach ($selected_services as $service_data) {
            OrderService::create([
                'order_id' => $order->id,
                'service_id' => $service_data->service_id,
            ]);

            PropagateService::create([
                'order_id' => $order->id,
                'service_id' => $service_data->service_id,
                'contract_id' => null,
                'setting_id' => null,
                'text' => $service_data->description ?? null,
            ]);
        }

        foreach ($selected_technicians as $technicianId) {
            $order_technicians[] = [
                'technician_id' => $technicianId,
                'order_id' => $order->id,
            ];
        }

        OrderTechnician::insert($order_technicians);

        // return redirect()->route('order.index');
        // Recupera la URL previa (si no hay, redirige a una ruta por defecto)
        $prevUrl = session()->get('prev_url', route('order.index'));

        // Limpiar la sesión
        session()->forget('prev_url');
        return redirect($prevUrl)->with('success', 'Orden de servicio creada correctamente.');
    }

    public function storeSignature(Request $request)
    {
        $request->validate([
            'signature_name' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $order = Order::find($request->input('order_id'));
        $order->signature_name = $request->signature_name;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageBinary = base64_encode(file_get_contents($image->getRealPath()));
            $order->customer_signature = $imageBinary;
        }

        $order->save();

        return redirect()->back()->with('success', 'Firma guardada correctamente.');
    }

    public function search(Request $request)
    {
        $customer = $request->input('customer');
        $date = $request->input('date');
        $time = $request->input('time');
        $service = $request->input('service');
        $status = $request->input('status');
        $size = $request->input('size');

        $orders = Order::where('status_id', $status);

        if ($customer) {
            $searchTerm = '%' . $customer . '%';
            $customerIds = Customer::where('name', 'LIKE', $searchTerm)->get()->pluck('id');
            $orders = $orders->whereIn('customer_id', $customerIds);
        }

        if ($time) {
            $orders = $orders->whereTime('start_time', $time);
        }

        if ($date) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date));
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $orders = $orders->whereBetween('programmed_date', [$startDate, $endDate]);
        }

        if ($service) {
            $serviceName = '%' . $service . '%';
            $serviceIds = Service::where('name', 'LIKE', $serviceName)->get()->pluck('id');
            $orderIds = OrderService::whereIn('service_id', $serviceIds)->get()->pluck('order_id');
            $orders = $orders->whereIn('id', $orderIds);
        }

        $size = $size ?? $this->size;
        $order_status = OrderStatus::all();
        $orders = $orders->orderByRaw("CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED) ASC")->paginate($size)->appends([
            'customer' => $customer,
            'date' => $date,
            'time' => $time,
            'service' => $service,
            'status' => $status,
            'size' => $size,
        ]);

        return view(
            'order.index',
            compact(
                'orders',
                'order_status',
                'size'
            )
        );
    }

    public function searchService(Request $request, $type)
    {
        $serviceIdsArray = [];
        $services = [];

        if ($type == 0) {
            $pestsJson = $request->input('pests');       // Obtén la cadena JSON
            $app_methodsJson = $request->input('app_methods'); // Obtén la cadena JSON

            $pestsArray = json_decode($pestsJson, true);       // Decodifica la cadena JSON a un arreglo asociativo
            $app_methodsArray = json_decode($app_methodsJson, true); // Decodifica la cadena JSON a un arreglo asociativo
            $has_pests = $request->input('has_pests');
            $has_app_methods = $request->input('has_app_methods');

            if ($has_pests != null && $has_app_methods != null) {
                if ($has_pests == 0 && $has_app_methods == 0) {
                    $serviceIdsArray = Service::where('has_pests', $has_pests)->where('has_application_methods', $has_app_methods)->pluck('id')->toArray();
                } else {
                    if (count($app_methodsArray) > 0) {
                        if (count($pestsArray) <= 0) {
                            $serviceIdsArray = ApplicationMethodService::whereIn('application_method_id', $app_methodsArray)->pluck('service_id')->toArray();
                            $serviceIdsArray = Service::whereIn('id', $serviceIdsArray)->where('has_pests', 0)->pluck('id')->toArray();
                        } else {
                            $serviceIdsArray = DB::table('application_method_service')
                                ->join('pest_service', 'application_method_service.service_id', '=', 'pest_service.service_id')
                                ->whereIn('pest_service.pest_id', $pestsArray)
                                ->whereIn('application_method_service.application_method_id', $app_methodsArray)
                                ->pluck('application_method_service.service_id')
                                ->toArray();
                        }
                    }
                }
            }
        } else {
            $request->validate([
                'search_service_input' => 'required|string',
            ]);
            $serviceIdsArray = Service::where('name', 'like', '%' . $request->input('search_service_input') . '%')->pluck('id');
        }

        $services = Service::whereIn('id', $serviceIdsArray)->get();
        $pests = [];
        $app_methods = [];
        $service_types = [];
        $business_lines = [];

        foreach ($services as $service) {
            $pests[] = $service->pests()->pluck('name');
            $app_methods[] = $service->appMethods()->pluck('name');
            $service_types[] = $service->serviceType()->pluck('name');
            $business_lines[] = $service->businessLine()->pluck('name');
        }

        return response()->json([
            'services' => $services,
            'pests' => $pests,
            'app_methods' => $app_methods,
            'service_types' => $service_types,
            'business_lines' => $business_lines,
            'pest_categories' => PestCategory::all(),
            'application_methods' => ApplicationMethod::all(),
            'show' => count($serviceIdsArray) > 0 ? true : false,
        ]);
    }

    public function searchCustomer(Request $request)
    {
        $customer_ids = $request->input('customer_ids');
        $name = $request->input('customer_name');
        $phone = $request->input('customer_phone');
        $address = $request->input('customer_address');

        if ($customer_ids) {
            $customer_ids = json_decode($customer_ids);
            $customers = Customer::whereIn('id', $customer_ids)->get();

            return response()->json([
                'customers' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'code' => $customer->code,
                        'name' => $customer->name,
                        'address' => $customer->address,
                        'type' => $customer->serviceType->name
                    ];
                })
            ]);
        }

        // Consulta para sedes (manteniendo condiciones originales)
        $sedesQuery = Customer::where('service_type_id', '!=', 1)
            ->where('general_sedes', '!=', 0)
            ->when($name, fn($q) => $q->where('name', 'like', "%$name%"))
            ->when($phone, fn($q) => $q->where('phone', 'like', "%$phone%"))
            ->when($address, fn($q) => $q->where('address', 'like', "%$address%"));

        $matrixs = $sedesQuery->get()->pluck('general_sedes');

        // Consulta para clientes principales (manteniendo condiciones originales)
        $customersQuery = Customer::where('status', '!=', 0)
            //->where('service_type_id', 1)
            ->when($name, fn($q) => $q->where('name', 'like', "%$name%"))
            ->when($phone, fn($q) => $q->where('phone', 'like', "%$phone%"))
            ->when($address, fn($q) => $q->where('address', 'like', "%$address%"))
            ->whereNotIn('id', $matrixs);

        $results = $customersQuery->get()->merge($sedesQuery->get());

        return response()->json([
            'customers' => $results->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'address' => $customer->address,
                    'type' => $customer->serviceType->name
                ];
            }),
        ]);
    }

    public function show(string $id, string $section)
    {
        $order = null;
        $order = Order::find($id);
        $searchTerm = '%' . 'ORDER_' . $id . '%';
        $tablelog = DatabaseLog::where('sql_command', 'LIKE', $searchTerm)->get();

        return view(
            'order.show',
            with(
                compact(
                    'order',
                    'section',
                    'tablelog'
                )
            )
        );
    }

    public function selectItems($comp_arr, $data_arr)
    {
        foreach ($data_arr as $data) {
            $data->checked = in_array($data->id, $comp_arr);
        }
        return $data_arr;
    }

    /*public function edit(string $id): View
    {
        try {
            $selected_services = [];
            $services_configuration = [];

            $cost = 0;
            $order = Order::find($id);

            if (!isset($order->customer_id)) {
                $error = 'No se ha seleccionado un cliente.';
                return view(
                    'order.index',
                    with(
                        compact(
                            'orders',
                            'order_status',
                            'error'
                        )
                    )
                );
            }

            $orders = Order::orderBy('id', 'desc')->get();
            $order_status = OrderStatus::all();
            $customer = Customer::find($order->customer_id);

            foreach ($order->services as $service) {
                $selected_services[] = [
                    'id' => $service->id,
                    'prefix' => $service->prefix,
                    'name' => $service->name,
                    'type' => [$service->serviceType->name],
                    'line' => [$service->businessLine->name],
                    'cost' => $service->cost,
                    'propagate_description' => $order->propagateByService($service->id)->text ?? null,
                ];

                $services_configuration[] = [
                    'service_id' => $service->id,
                    'description' => $order->propagateByService($service->id)->text ?? null,
                ];

                $cost += $service->cost;
            }

            $technicians = Technician::with('user')
                ->whereIn('user_id', Technician::pluck('user_id'))
                ->join('user', 'technician.user_id', '=', 'user.id')
                ->orderBy('user.name', 'ASC')
                ->select('technician.*')
                ->get();

            $navigation = [
                'Orden de servicio' => route('order.edit', ['id' => $order->id]),
                'Reporte' => route('report.review', ['id' => $order->id]),
                'Seguimientos' => route('tracking.create.order', ['id' => $order->id]),
            ];

            return view(
                'order.edit',
                compact(
                    'order',
                    'order_status',
                    'customer',
                    'technicians',
                    'selected_services',
                    'cost',
                    'navigation',
                    'services_configuration'
                )
            );
        } catch (\Exception $e) {
            // Log del error si es necesario
            Log::error('Error en OrderController@edit: ' . $e->getMessage());

            $error = 'Ocurrió un error al cargar la orden. Por favor, intente nuevamente.';
            return view('order.index', compact('error'));
        }
    }*/

    public function edit(string $id): View
    {
        try {
            $selected_services = [];
            $services_configuration = [];
            $cost = 0;

            // Cargar solo la orden específica con relaciones necesarias
            $order = Order::with(['services.serviceType', 'services.businessLine', 'customer'])
                ->findOrFail($id);

            if (!isset($order->customer_id)) {
                $error = 'No se ha seleccionado un cliente.';
                return view('order.index', compact('error'));
            }

            // Eliminar estas líneas que consumen mucha memoria
            // $orders = Order::orderBy('id', 'desc')->get(); // ❌ Esto carga TODAS las órdenes
            // $order_status = OrderStatus::all(); // ❌ Esto carga TODOS los estados

            $customer = $order->customer; // Ya viene cargado con with()

            foreach ($order->services as $service) {
                $selected_services[] = [
                    'id' => $service->id,
                    'prefix' => $service->prefix,
                    'prefix_name' => $service->prefixType->name,
                    'name' => $service->name,
                    'type' => [$service->serviceType->name],
                    'line' => [$service->businessLine->name],
                    'cost' => $service->cost,
                    'propagate_description' => $order->propagateByService($service->id)->text ?? null,
                ];

                $services_configuration[] = [
                    'service_id' => $service->id,
                    'setting_id' => $order->setting_id,
                    'contract_id' => $order->contract_id,
                    'description' => $order->propagateByService($service->id)->text ?? null,
                ];

                $cost += $service->cost;
            }

            // Optimizar la consulta de técnicos
            $technicians = Technician::with('user')
                ->join('user', 'technician.user_id', '=', 'user.id')
                ->orderBy('user.name', 'ASC')
                ->select('technician.*')
                ->get();

            $navigation = [
                'Orden de servicio' => route('order.edit', ['id' => $order->id]),
                'Reporte' => route('report.review', ['id' => $order->id]),
                'Seguimientos' => route('tracking.create.order', ['id' => $order->id]),
            ];

            // Si necesitas order_status para la vista, cargar solo lo necesario
            $order_status = OrderStatus::select('id', 'name')->get();
            $view = 'order';

            return view(
                'order.edit',
                compact(
                    'order',
                    'order_status',
                    'customer',
                    'technicians',
                    'selected_services',
                    'cost',
                    'navigation',
                    'services_configuration',
                    'view'
                )
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $error = 'La orden no fue encontrada.';
            return view('order.index', compact('error'));

        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en OrderController@edit - ID: ' . $id . ' - Error: ' . $e->getMessage());

            $error = 'Ocurrió un error al cargar la orden. Por favor, intente nuevamente.';
            return view('order.index', compact('error'));
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        //dd($request->all());
        $updated_services = [];
        $updated_techncians = [];
        $updated_propagations = [];

        $selected_services = json_decode($request->input('services'));
        $selected_technicians = json_decode($request->input('technicians'));

        $order = Order::find($id);

        if ($request->missing('technicians')) {
            $error = 'No se ha seleccionado un técnico.';
            return back();
        }

        // Guardar el pedido
        $order->fill($request->all());
        if ($request->status_id == 1) {
            $order->closed_by = null;
        }
        $order->updated_at = now();
        $order->save();

        foreach ($selected_services as $service_data) {
            $os = OrderService::updateOrCreate([
                'order_id' => $order->id,
                'service_id' => $service_data->service_id,
            ], [
                'updated_at' => now(),
            ]);

            $updated_services[] = $os->id;

            $ps = PropagateService::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'service_id' => $service_data->service_id,
                    'contract_id' => $service_data->contract_id ?? null,
                    'setting_id' => $service_data->setting_id ?? null,
                ],
                [
                    'text' => $service_data->description ?? null,
                    'updated_at' => now(),
                ]
            );

            $updated_propagations[] = $ps->id;
        }

        foreach ($selected_technicians as $technicianId) {
            $ot = OrderTechnician::updateOrCreate(
                [
                    'technician_id' => $technicianId,
                    'order_id' => $order->id,
                ],
                [
                    'updated_at' => now(),
                ]
            );

            $updated_techncians[] = $ot->id;
        }

        OrderService::where('order_id', $order->id)->whereNotIn('id', $updated_services)->delete();
        OrderTechnician::where('order_id', $order->id)->whereNotIn('id', $updated_techncians)->delete();
        PropagateService::where('order_id', $order->id)->whereNotIn('id', $updated_propagations)->delete();

        if ($request->replicate_execution) {
            $services = $order->services();
            $orders = Order::where('customer_id', $order->customer_id)
                ->whereIn('id', OrderService::whereIn('service_id', $services->pluck('service.id'))->pluck('order_id'))
                ->where('status_id', 1)
                ->get();
            foreach ($orders as $ord) {
                $ord->execution = $order->execution;
                $ord->save();
            }
        }

        if ($request->replicate_areas) {
            $services = $order->services();
            $orders = Order::where('customer_id', $order->customer_id)
                ->whereIn('id', OrderService::whereIn('service_id', $services->pluck('service.id'))->pluck('order_id'))
                ->where('status_id', 1)
                ->get();

            foreach ($orders as $ord) {
                $ord->areas = $order->areas;
                $ord->save();
            }
        }

        if ($request->replicate_comments) {
            $services = $order->services();
            $orders = Order::where('customer_id', $order->customer_id)
                ->whereIn('id', OrderService::whereIn('service_id', $services->pluck('service.id'))->pluck('order_id'))
                ->where('status_id', 1)
                ->get();

            foreach ($orders as $ord) {
                $ord->additional_comments = $order->additional_comments;
                $ord->save();
            }
        }

        /*if ($order->technicians()->count() == 1 && $request->status_id != 1) {
            $order->update(['closed_by' => $order->technicians()->first()->user_id]);
        }*/

        return back();
    }

    public function cancel(string $id): RedirectResponse
    {
        $order = Order::find($id);
        if ($order) {
            $order->status_id = 5;
            $order->save();
        }
        return back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $order = Order::find($id);
        $order->delete();
        return back();
    }

    private function setFile($data, $name, $extension = 'png')
    {
        $file_name = $name . '.' . $extension;
        $directory = storage_path($this->files_path);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = $directory . '/' . $file_name;
        file_put_contents($path, $data);
        return $path;
    }

    public function getControlPoints(Request $request)
    {
        $floorplanID = $request->input('floorplan_id');
        $orderID = $request->input('order_id');
        $version = $request->input('version');
        $data = [];

        $devices = Device::where('floorplan_id', $floorplanID)->where('version', $version)->get();

        foreach ($devices as $device) {
            $questions = [];
            $incidents = $device->incidents()->where('order_id', $orderID)->get();
            foreach ($incidents as $incident) {
                $questions[] = [
                    'optionID' => $incident->question->option->id,
                    'question' => $incident->question()->first()->question,
                    'answer' => $incident->answer,
                ];
            }

            $data[] = [
                'deviceID' => $device->id,
                'nplan' => $device->nplan,
                'name' => optional($device->controlPoint->product)->name,
                'zone' => $device->applicationArea()->first()->name ?? '-',
                'questions' => !empty($questions) ? $questions : $device->questions()->get(),
            ];
        }
        return response()->json($data);
    }

    public function filter(Request $request)
    {
        // dd($request->all());
        // Obtener parámetros de ordenamiento
        $size = $request->input('size');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'DESC');

        // Construir consulta base
        $query = Order::query();

        // Aplicar filtros (mantén tus filtros existentes)
        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . $request->input('folio') . '%');
        }

        if ($request->filled('customer')) {
            $searchTerm = '%' . $request->input('customer') . '%';
            $customerIds = Customer::where('name', 'LIKE', $searchTerm)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->input('status'));
        }

        if ($request->filled('service')) {
            $serviceName = '%' . $request->input('service') . '%';
            $serviceIds = Service::where('name', 'LIKE', $serviceName)->pluck('id');
            $orderIds = OrderService::whereIn('service_id', $serviceIds)->pluck('order_id');
            $query->whereIn('id', $orderIds);
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('programmed_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);
        }

        if ($request->filled('time')) {
            $query->whereTime('start_time', $request->input('time'));
        }

        if ($request->filled('order_type')) {
            if ($request->input('order_type') == 'MIP') {
                $query->where('contract_id', '>', 0);
            } else {
                $query->whereNull('contract_id');
            }
        }

        if ($request->filled('signature_status')) {
            if ($request->input('signature_status') == 'signed') {
                $query->whereNotNull('customer_signature');
            } elseif ($request->input('signature_status') == 'unsigned') {
                $query->whereNull('customer_signature');
            }
        }

        if ($request->filled('technician')) {
            $technicianId = $request->input('technician');
            $orderIds = OrderTechnician::where('technician_id', $technicianId)->pluck('order_id');
            $query->whereIn('id', $orderIds);
        }

        // Aplicar ordenamiento después de los filtros
        $size = $size ?? $this->size;

        $orders = $query->join('customer', 'order.customer_id', '=', 'customer.id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED) ASC")
            ->orderBy('programmed_date')
            ->orderBy('customer.name', $direction)
            ->select('order.*')
            ->with('customer');

        // Paginar resultados
        $orders = $query->paginate($size)
            ->appends($request->all());

        $order_status = OrderStatus::all();
        $customer_ranges = Customer::where('general_sedes', '!=', 0)->orWhere('service_type_id', 1)->orderBy('name', 'asc')->get();
        $size = $this->size;
        $navigation = $this->navigation;

        $technicians = Technician::with('user')
            ->whereIn('user_id', Technician::pluck('user_id'))
            ->join('user', 'technician.user_id', '=', 'user.id')
            ->orderBy('user.name', 'ASC')
            ->select('technician.*')
            ->get();

        return view(
            'order.index',
            compact(
                'orders',
                'order_status',
                'size',
                'customer_ranges',
                'technicians',
                'navigation'
            )
        );
    }

    public function getTechniciansInRange(Request $request)
    {
        $tech_data = [];
        try {
            $customer_id = $request->input('customer_id');
            $date = $request->input('date');

            // Validación básica de los parámetros requeridos
            if (!$customer_id) {
                return response()->json(['error' => 'El parámetro customer_id es requerido'], 400);
            }

            $orders = Order::where('customer_id', $customer_id)->where('status_id', 1);

            if ($date) {
                try {
                    [$startDate, $endDate] = array_map(function ($d) {
                        return Carbon::createFromFormat('d/m/Y', trim($d));
                    }, explode(' - ', $date));

                    $startDate = $startDate->format('Y-m-d');
                    $endDate = $endDate->format('Y-m-d');

                    $orders = $orders->whereBetween('programmed_date', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Formato de fecha inválido. Use el formato dd/mm/yyyy - dd/mm/yyyy',
                    ], 400);
                }
            }

            $orders = $orders->get();
            $technicians = [];

            foreach ($orders as $order) {
                foreach ($order->technicians as $technician) {
                    if (!in_array($technician->id, $technicians)) {
                        $technicians[] = $technician->id;
                    }
                }
            }

            $found_technicians = Technician::whereIn('id', $technicians)->get();
            $technicians = Technician::with('user')
                ->whereIn('user_id', Technician::pluck('user_id'))
                ->join('user', 'technician.user_id', '=', 'user.id')
                ->orderBy('user.name', 'ASC')
                ->select('technician.*')
                ->get();

            foreach ($technicians as $tech) {
                $tech_data[] = [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'is_assigned' => in_array($tech->id, $found_technicians->pluck('id')->toArray()),
                ];
            }

            return response()->json([
                'technicians' => $tech_data,
                'orders' => $orders->pluck('id')->toArray(),
                'show' => count($tech_data) > 0,
            ], 200);

        } catch (\Exception $e) {
            // Log del error (recomendado)
            Log::error('Error en getTechniciansInRange: ' . $e->getMessage());

            return response()->json([
                'error' => 'Ocurrió un error al procesar la solicitud',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function assignTechnicians(Request $request)
    {
        $technicians = json_decode($request->input('technicians'));
        $orders = json_decode($request->input('orders'));

        $updated_technicians = [];

        if (!$orders || !$technicians) {
            return response()->json(['error' => 'Parámetros incompletos'], 400);
        }

        try {
            $orders = Order::whereIn('id', $orders)->where('status_id', 1)->get();

            foreach ($orders as $order) {
                foreach ($technicians as $techId) {
                    OrderTechnician::updateOrCreate([
                        'order_id' => $order->id,
                        'technician_id' => $techId,
                    ]);
                    $updated_technicians[] = $techId;
                }
                OrderTechnician::where('order_id', $order->id)
                    ->whereNotIn('technician_id', $updated_technicians)
                    ->delete();
            }

            return response()->json(['success' => 'Técnicos asignados correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al asignar técnicos: ' . $e->getMessage()], 500);
        }
    }
}
