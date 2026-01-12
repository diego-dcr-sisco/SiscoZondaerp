<?php

namespace App\Http\Controllers;

use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\PropagateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\ExecFrequency;
use App\Models\User;
use App\Models\PestCategory;
use App\Models\Day;
use App\Models\Technician;
use App\Models\ContractTechnician;
use App\Models\Order;
use App\Models\OrderTechnician;
use App\Models\ContractService;
use App\Models\Administrative;
use App\Models\Contract_File;
use App\Models\Service;
use App\Models\ServicePrefix;

use Carbon\Carbon;

class ContractController extends Controller
{
    private static $files_path = 'files/';

    private $mip_directories = [
        'MIP',
        'Contrato de servicio',
        'Justificación',
        'Datos de la empresa',
        'Certificación MIP',
        'Plano de ubicación de dispositivos',
        'Responsabilidades',
        'Plago objeto',
        'Calendarización de actividades',
        'Descripción de actividades POEs',
        'Métodos preventivos',
        'Métodos correctivos',
        'Información de plaguicidas',
        'Reportes',
        'Gráficas de tendencias',
        'Señaléticas',
        'Pago seguro'
    ];

    private $intervals = [
        'Por día',
        'Primera semana',
        'Segunda semana',
        'Tercera semana',
        'Cuarta semana',
        'Ultima semana',
        'Quincenal'
    ];

    private $size = 50;
    protected $navigation;


    private function extractNumberFromFolio(string $folio): ?int
    {
        $parts = explode('-', $folio);
        return isset($parts[1]) ? (int) $parts[1] : null;
    }

    public function __construct()
    {
        $this->navigation = [
            'Ordenes de servicio' => route('order.index'),
            'Contratos' => route('contract.index'),
            'Facturas' => route('invoices.index'),
            'Seguimientos' => route('crm.tracking'),
        ];
    }

    public function index(): View
    {
        $contracts = Contract::orderBy('id', 'desc')->paginate($this->size);
        $technicians = Technician::all();
        $navigation = $this->navigation;

        return view(
            'contract.index',
            compact(
                'contracts',
                'technicians',
                'navigation'
            )
        );
    }

    public function create(): View
    {
        $technicians = Technician::with('user')
            ->join('user as u', 'technician.user_id', '=', 'u.id')
            ->where('u.status_id', 2)
            ->orderBy('u.name', 'ASC')
            ->select('technician.*', 'u.name as user_name')
            ->get();

        $frequencies = ExecFrequency::all();
        $contracts = Contract::all();
        $intervals = $this->intervals;
        $can_renew = false;

        $prefixes = ServicePrefix::pluck('name', 'id');
        $view = 'contract';

        return view(
            'contract.create',
            compact(
                'frequencies',
                'technicians',
                'contracts',
                'intervals',
                'can_renew',
                'prefixes',
                'view'
            )
        );
    }

    private function extractOrderNumber($folio)
    {
        $parts = explode('-', $folio);
        return (int) end($parts);
    }

    public function store(Request $request): RedirectResponse
    {
        //dd($request->all());
        $configurations = json_decode($request->input('configurations'));
        $selected_technicians = json_decode($request->input('technicians'));

        $start_date = $request->input('startdate');
        $end_date = $request->input('enddate');
        $customer_id = $request->input('customer_id');

        if ($start_date == null || $end_date == null || $customer_id == null) {
            return redirect()->back()->withErrors(['error' => 'Faltan datos obligatorios: cliente, fecha de inicio o fecha de fin.']);
        }

        $customer = Customer::find($customer_id);

        $contract = Contract::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::user()->id,
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'status' => 1,
            'file' => null,
        ]);

        $last_order = Order::where('customer_id', $customer->id)
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $count_orders = $last_order ? $this->extractOrderNumber($last_order->folio) + 1 : 1;

        foreach ($selected_technicians as $id) {
            ContractTechnician::insert([
                'technician_id' => $id,
                'contract_id' => $contract->id,
            ]);
        }

        foreach ($configurations as $data) {
            $contract_service = ContractService::create([
                'contract_id' => $contract->id,
                'service_id' => $data->service_id,
                'execution_frequency_id' => $data->frequency_id,
                'interval' => $data->interval_id ?? 1, // Valor por defecto 1 si es null o 0
                'days' => json_encode($data->days),
                'total' => count($data->dates),
                'service_description' => $data->description ?? null,
                'created_at' => now(),
            ]);


            foreach ($data->dates as $date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $order = Order::create([
                    'administrative_id' => Administrative::where('user_id', Auth::user()->id)->first()->id,
                    'customer_id' => $customer->id,
                    'contract_id' => $contract->id,
                    'setting_id' => $contract_service->id,
                    'status_id' => '1',
                    'start_time' => '00:00',
                    'programmed_date' => $formattedDate,
                    //'folio' => $customer->code . '-' . $count_orders,
                    'folio' => $customer->code . '.' . ($contract->id ? ('MIP' . $contract->id) : 'SEG') . '-' . $count_orders,
                ]);

                OrderService::insert([
                    'order_id' => $order->id,
                    'service_id' => $data->service_id,
                ]);

                PropagateService::create([
                    'order_id' => $order->id,
                    'service_id' => $data->service_id,
                    'contract_id' => $contract->id,
                    'setting_id' => $contract_service->id,
                    'text' => $data->description ?? null
                ]);

                foreach ($selected_technicians as $id) {
                    OrderTechnician::insert([
                        'technician_id' => $id,
                        'order_id' => $order->id,
                    ]);
                }
                $count_orders++;
            }
        }
        return redirect()->route('contract.index');
    }

    public function search(Request $request)
    {
        $size = $request->input('size');
        $direction = $request->input('direction', 'DESC');
        $query = Contract::query();

        if ($request->filled('customer')) {
            $customers = Customer::where('name', 'LIKE', '%' . $request->customer . '%')->get();
            $query = $query->whereIn('customer_id', $customers->pluck('id'));
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query = $query->where('startdate', '>=', $startDate)->where('enddate', '<=', $endDate);
        }

        $contracts = $query->orderBy('startdate', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());
        $technicians = Technician::all();

        return view(
            'contract.index',
            compact(
                'contracts',
                'technicians',
            )
        );
    }

    public function searchOrders(Request $request, string $id)
    {
        //dd($request->all());
        // Obtener parámetros de ordenamiento
        $size = $request->input('size');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'DESC');
        $contract = Contract::find($id);

        // Construir consulta base
        $query = Order::where('contract_id', $contract->id);

        // Aplicar filtros (mantén tus filtros existentes)
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
                $endDate->format('Y-m-d')
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
                $query->whereNotNull('signature_name');
            } elseif ($request->input('signature_status') == 'unsigned') {
                $query->whereNull('signature_name');
            }
        }

        // Aplicar ordenamiento después de los filtros
        $query->orderBy($sort, $direction);
        $size = $size ?? $this->size;

        // Paginar resultados
        $orders = $query->paginate($size)
            ->appends($request->all());

        $order_status = OrderStatus::all();
        $customer_ranges = Customer::where('general_sedes', '!=', 0)->orWhere('service_type_id', 1)->orderBy('name', 'asc')->get();
        $size = $this->size;

        return view(
            'contract.show',
            compact(
                'contract',
                'orders',
                'order_status',
                'size',
                'customer_ranges'
            )
        );
    }

    public function show(string $id)
    {
        $contract = Contract::find($id);
        $order_status = OrderStatus::all();
        $orders = Order::where('contract_id', $contract->id)->orderBy('programmed_date')->paginate($this->size);
        $customer = $contract->customer();
        return view('contract.show', compact('contract', 'orders', 'order_status', 'customer'));
    }

    public function getSelectedTechnicians(Request $request)
    {
        $technicians = Technician::all();
        $technicianIds = ContractTechnician::where('contract_id', $request->contractId)->get();
        $technicianSelected = empty(array_diff($technicians->pluck('id')->toArray(), $technicianIds->pluck('technician_id')->toArray())) ? [0] : $technicianIds->pluck('technician_id')->toArray();

        return response()->json([
            'technicians' => $technicians->pluck('id')->toArray(),
            'technicianSelected' => $technicianSelected,
        ]);
    }

    public function updateTechnicians(Request $request, int $id)
    {
        $ot_array = json_decode($request->input('technicians'));
        $contract = Contract::find($id);

        if (empty($ot_array)) {
            return redirect()->back();
        }

        $technicians = ContractTechnician::where('contract_id', $id)->pluck('technician_id')->toArray();

        if ($ot_array[count($ot_array) - 1] == 0) {
            $existingTechnicians = Technician::pluck('id')->toArray();
            $techniciansInsert = array_diff($existingTechnicians, $technicians);
        } else {
            $techniciansInsert = array_diff($ot_array, $technicians);
            $techniciansDelete = array_diff($technicians, $ot_array);

            foreach ($techniciansDelete as $technicianId) {
                ContractTechnician::where('contract_id', $id)
                    ->where('technician_i  d', $technicianId)
                    ->delete();
            }
        }

        foreach ($techniciansInsert as $technicianId) {
            ContractTechnician::updateOrCreate(
                ['contract_id' => $id, 'technician_id' => $technicianId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $orders = $contract->orders;
        $orderIds = $orders->pluck('id')->toArray();
        $orderTechnicians = OrderTechnician::whereIn('order_id', $orderIds)->get();

        foreach ($orders as $order) {
            $newTechnicians = $order->contract->technicians()->pluck('technician.id')->toArray();
            $orderTechniciansToDelete = $orderTechnicians->where('order_id', $order->id)
                ->whereNotIn('technician_id', $newTechnicians);

            foreach ($orderTechniciansToDelete as $orderTechnicianToDelete) {
                $orderTechnicianToDelete->delete();
            }

            $techniciansToAdd = array_diff($newTechnicians, $orderTechnicians->pluck('technician_id')->toArray());

            foreach ($techniciansToAdd as $technicianToAdd) {
                OrderTechnician::create([
                    'order_id' => $order->id,
                    'technician_id' => $technicianToAdd
                ]);
            }
        }

        return redirect()->back();
    }

    public function edit(string $id)
    {
        $contract = Contract::find($id);
        $technicians = Technician::with('user')
            ->join('user as u', 'technician.user_id', '=', 'u.id')
            ->where('u.status_id', 2)
            ->orderBy('u.name', 'ASC')
            ->select('technician.*', 'u.name as user_name')
            ->get();

        $frequencies = ExecFrequency::all();
        $contracts = Contract::all();
        $intervals = $this->intervals;

        $configurations = [];
        $selected_services = [];
        $count_indexs = [];

        $contract_services = ContractService::where('contract_id', $contract->id)->get();
        $service_ids = $contract_services->pluck('service_id')->unique()->toArray();
        $services = Service::whereIn('id', $service_ids)->get();

        foreach ($services as $service) {
            $setting = $contract->setting($service->id);
            $selected_services[] = [
                'id' => $service->id,
                'prefix' => $service->prefix,
                'name' => $service->name,
                'type' => $service->serviceType->name,
                'line' => $service->businessLine->name,
                'cost' => $service->cost,
                'description' => $setting->service_description ?? $service->description ?? null,
                #settings: [],
            ];
        }

        foreach ($contract_services as $index => $cs) {
            if (!isset($count_indexs[$cs->service_id])) {
                $count_indexs[$cs->service_id] = 1;
            } else {
                $count_indexs[$cs->service_id]++;
            }

            $orders = Order::where('setting_id', $cs->id)->orderBy('programmed_date')->get();
            $configurations[] = [
                'config_id' => $count_indexs[$cs->service_id],
                'setting_id' => $cs->id,
                'service_id' => $cs->service_id,
                'frequency' => $cs->execfrequency->name,
                'frequency_id' => $cs->execution_frequency_id,
                'interval' => $this->intervals[$cs->interval],
                'interval_id' => $cs->interval,
                'days' => explode(',', json_decode($cs->days)[0] ?? ''),
                'dates' => $orders->pluck('programmed_date')->map(function ($date) {
                    return $date . 'T00:00:00.000Z';
                })->toArray(),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'folio' => $order->folio,
                        'programmed_date' => $order->programmed_date . 'T00:00:00.000Z',
                        'status_id' => $order->status_id,
                        'status_name' => $order->status->name,
                        'url' => route('order.edit', ['id' => $order->id])
                    ];
                })->toArray(), // ← Convertir a array
                'description' =>  $cs->service_description ?? null,
            ];
        }

        /*$customer = [
            'id' => $contract->customer_id,
            'name' => $contract->customer->name,
            'code' => $contract->customer->code,
            'address' => $contract->customer->address,
            'type' => $contract->customer->serviceType->name
        ];*/

        $prefixes = ServicePrefix::pluck('name', 'id');
        $can_renew = false;
        $view = 'contract';

        return view(
            'contract.edit',
            compact(
                'contract',
                'frequencies',
                'technicians',
                'contracts',
                'intervals',
                'selected_services',
                'configurations',
                'can_renew',
                'prefixes',
                'view'
            )
        );
    }

    public function update(Request $request, string $id)
    {
        //dd($request->all());
        //dd(json_decode($request->all()['configurations']));
        $updated_settings = [];
        $updated_orders = [];
        $keep_settings = [];

        $has_services_updated = false;
        $configurations = json_decode($request->input('configurations'));
        $selected_technicians = json_decode($request->input('technicians'));
        $updated_services = json_decode($request->input('updated_services'));
        //$delete_settings = json_decode($request->input('delete_settings'));
        //$was_services_updated = $request->input('was_services_updated') === 'true';

        $contract = Contract::findOrFail($id);
        $contract->update($request->only(['startdate', 'enddate']));

        if (count($updated_services) > 0) {
            $has_services_updated = true;
        }

        if ($has_services_updated) {
            $aux_configurations = array_filter($configurations, function ($config) use ($updated_services) {
                return in_array($config->service_id, $updated_services);
            });

            //dd($aux_configurations);

            foreach ($aux_configurations as $data) {
                $contract_service = ContractService::find($data->setting_id);
                //dd($contract_service);
                //$allSettingsIds = array_column($configurations, 'setting_id');
                //dd($allSettingsIds);

                if (!$contract_service) {
                    $contract_service = ContractService::create([
                        'contract_id' => $contract->id,
                        'service_id' => $data->service_id,
                        'execution_frequency_id' => $data->frequency_id,
                        'interval' => $data->interval_id ?? 1, // Valor por defecto 1 si es null
                        'days' => json_encode($data->days),
                        'total' => count($data->dates),
                        'service_description' => $data->description ?? null,
                        'created_at' => now(),
                    ]);
                }

                $updated_settings[] = $contract_service->id;

                foreach ($data->orders as $order) {
                    if (str_starts_with($order->id, 'temp_')) {
                        $existing_order = Order::create(
                            [
                                'contract_id' => $contract->id,
                                'setting_id' => $contract_service->id,
                                'programmed_date' => Carbon::parse($order->programmed_date)->format('Y-m-d'),
                                'administrative_id' => Administrative::where('user_id', Auth::user()->id)->first()->id,
                                'customer_id' => $contract->customer_id,
                                'status_id' => $order->status_id,
                                'start_time' => '00:00',
                            ]
                        );

                        OrderService::create([
                            'order_id' => $existing_order->id,
                            'service_id' => $data->service_id,
                        ]);

                        PropagateService::create([
                            'order_id' => $existing_order->id,
                            'service_id' => $data->service_id,
                            'contract_id' => $contract->id,
                            'setting_id' => $contract_service->id,
                            'text' => $data->description ?? null
                        ]);

                    } else {
                        $existing_order = Order::find($order->id);
                        if ($existing_order && $existing_order->status_id == 1) {
                            $existing_order->update([
                                'setting_id' => $contract_service->id,
                                'programmed_date' => Carbon::parse($order->programmed_date)->format('Y-m-d'),
                            ]);

                            $s_propagate = PropagateService::where('order_id', $existing_order->id)
                            ->where('service_id', $data->service_id)
                            ->where('contract_id', $contract->id)
                            ->where('setting_id', $contract_service->id)
                            ->first();
                            
                            if (!$s_propagate) {
                                PropagateService::create([
                                    'order_id' => $existing_order->id,
                                    'service_id' => $data->service_id,
                                    'contract_id' => $contract->id,
                                    'setting_id' => $contract_service->id,
                                    'text' => $data->description ?? null
                                ]);
                            } else {
                                $s_propagate->update([
                                    'text' => $data->description ?? null
                                ]);
                            }
                        }

                        if($existing_order && $existing_order->status_id != 1) {
                            $keep_settings[] = $existing_order->setting_id;
                        }

                        /*$s_order = OrderService::where('order_id', $existing_order->id)
                            ->where('service_id', $data->service_id)
                            ->first();
                        if (!$s_order) {
                            OrderService::create([
                                'order_id' => $existing_order->id,
                                'service_id' => $data->service_id,
                            ]); 
                        }*/
                    }
                    $updated_orders[] = $existing_order->id;
                }
            }
        }

        $delete_settings = ContractService::where('contract_id', $contract->id)
            ->whereNotIn('id', $updated_settings)
            ->whereNotIn('id', $keep_settings)
            ->whereIn('service_id', $updated_services)
            ->get();

        $delete_orders = Order::where('contract_id', $contract->id)
            ->whereNotIn('id', $updated_orders)
            ->whereIn('setting_id', $updated_settings)
            ->orWhereIn('setting_id', $delete_settings->pluck('id'))
            ->where('status_id', 1)
            ->get();


        if ($delete_settings->isNotEmpty()) {
            $delete_settings->each(function ($setting) {
                $setting->delete();
            });
        }

        if ($delete_orders->isNotEmpty()) {
            $delete_orders->each(function ($order) {
                $order->delete();
            });

            OrderService::whereIn('order_id', $delete_orders->pluck('id'))->delete();
            OrderTechnician::whereIn('order_id', $delete_orders->pluck('id'))->delete();
        }

        $this->generateOrderFolios($contract);
        $this->updateContractTechnicians($contract, $selected_technicians);
        return back()->with('success', 'Contrato y órdenes actualizados correctamente');
    }

    protected function findOrCreateContractService($contract, $data, $setting)
    {
        // Primero buscamos coincidencia exacta
        $contract_service = ContractService::find($setting->id);

        if (!$contract_service) {
            $contract_service = ContractService::create([
                'contract_id' => $contract->id,
                'service_id' => $data->id,
                'execution_frequency_id' => $setting->frequency,
                'interval' => $setting->interval,
                'days' => json_encode($setting->days),
                'service_description' => $setting->description ?? null,
                'total' => count($setting->dates),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $contract_service->update([
                'total' => count($setting->dates),
                'service_description' => $setting->description ?? null,
                'updated_at' => now()
            ]);
        }

        return $contract_service;
    }

    // Método auxiliar para manejar órdenes duplicadas
    protected function handleDuplicateOrders($contract, $contract_service, $date, $service_id)
    {
        // 1. Buscar todas las órdenes para este servicio en la fecha específica
        $orders = Order::where('contract_id', $contract->id)
            ->where('programmed_date', $date)
            ->whereHas('orderServices', function ($query) use ($service_id) {
                $query->where('service_id', $service_id);
            })
            ->where('setting_id', $contract_service->id)
            ->orderBy('status_id', 'asc')->orderBy('created_at', 'asc') // Las más recientes vieja
            ->get();

        // 2. Si no hay órdenes, no hay nada que hacer
        if ($orders->isEmpty()) {
            return null;
        }

        // 3. Si solo hay una orden
        if ($orders->count() == 1) {
            $order = $orders->first();

            // Actualizar el setting_id si es diferente
            if ($order->setting_id != $contract_service->id) {
                $order->update(['setting_id' => $contract_service->id, 'date' => $date]);
            }

            return $order;
        }

        // 4. Si hay múltiples órdenes
        $order_to_keep = null;

        // Priorizar órdenes con status != 1
        $settings_in_contract = ContractService::where('contract_id', $contract->id)
            ->pluck('id')
            ->toArray();

        $non_pending = $orders->where('status_id', '!=', 1)->first();
        if ($non_pending) {
            $order_to_keep = $non_pending;
        } else {
            // Si todas son pendientes, tomar la más vieja
            $order_to_keep = $orders->where('status_id', 1)->first();
        }

        // Actualizar el setting_id de la orden a conservar
        if ($order_to_keep->setting_id != $contract_service->id) {
            $order_to_keep->update(['setting_id' => $contract_service->id, 'date' => $date]);
        }

        // 5. Eliminar las demás órdenes
        $orders_to_delete = $orders->where('id', '!=', $order_to_keep->id);
        Order::whereIn('id', $orders_to_delete->pluck('id'))->delete();

        return $order_to_keep;
    }

    // Método auxiliar para crear/actualizar órdenes
    protected function createOrder($contract, $contract_service, $date, $service_id, $selected_technicians)
    {
        // Crear la nueva orden
        $new_order = Order::create([
            'administrative_id' => Administrative::where('user_id', Auth::user()->id)->first()->id,
            'customer_id' => $contract->customer_id,
            'contract_id' => $contract->id,
            'setting_id' => $contract_service->id,
            'status_id' => 1, // Pendiente
            'start_time' => '00:00',
            'programmed_date' => $date,
        ]);

        // Asociar el servicio
        OrderService::create([
            'order_id' => $new_order->id,
            'service_id' => $service_id,
        ]);

        // Asociar técnicos
        foreach ($selected_technicians as $technician_id) {
            OrderTechnician::create([
                'order_id' => $new_order->id,
                'technician_id' => $technician_id,
            ]);
        }

        // Crear registro en PropagateService
        PropagateService::create([
            'order_id' => $new_order->id,
            'service_id' => $service_id,
            'contract_id' => $contract->id,
            'setting_id' => $contract_service->id,
            'text' => $contract_service->service_description ?? null,
        ]);

        return $new_order;
    }

    // Método auxiliar para actualizar técnicos del contrato
    protected function updateContractTechnicians($contract, $selected_technicians)
    {
        ContractTechnician::where('contract_id', $contract->id)->delete();

        $technicians_data = array_map(function ($tech_id) use ($contract) {
            return [
                'contract_id' => $contract->id,
                'technician_id' => $tech_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $selected_technicians);

        ContractTechnician::insert($technicians_data);

        Order::where('contract_id', $contract->id)->where('status_id', 1)->each(function ($order) use ($selected_technicians) {
            OrderTechnician::where('order_id', $order->id)->delete();
            array_map(fn($id) => OrderTechnician::create(['order_id' => $order->id, 'technician_id' => $id]), $selected_technicians);
        });
    }

    // Método auxiliar para generar folios únicos
    protected function generateOrderFolios($contract)
    {
        $base_folio = $contract->customer->code . '.' . ($contract->id ? ('MIP' . $contract->id) : 'SEG');

        //$orders = Order::where('contract_id', $contract->id)->get();
        $highestNumber = Order::where('contract_id', $contract->id)
            ->whereNotNull('folio')
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(folio, "-", -1) AS UNSIGNED)) as max_number')
            ->value('max_number');

        $orders = Order::where('contract_id', $contract->id)
            ->whereNull('folio')
            ->orderBy('programmed_date', 'asc')
            ->get();

        $current_index = $highestNumber ? ($highestNumber + 1) : 1;

        foreach ($orders as $order) {
            $folio = $base_folio . '-' . $current_index;
            $order->update(['folio' => $folio]);
            $current_index++;
        }


        /*// Obtenemos todas las órdenes ordenadas por fecha programada
        $orders = Order::where('customer_id', $contract->customer->id)
            ->orderBy('programmed_date', 'asc')
            ->get();

        // Primero procesamos las órdenes con status_id = 5 para respetar sus folios existentes
        $used_indices = [];
        foreach ($orders as $order) {
            if (
                $order->status_id == 5 && !empty($order->folio) &&
                str_starts_with($order->folio, $base_folio)
            ) {
                $parts = explode('-', $order->folio);
                $index = (int) end($parts);
                $used_indices[$index] = true; // Marcamos este índice como usado
            }
        }

        // Luego asignamos folios a las demás órdenes
        $current_index = 1;
        foreach ($orders as $order) {
            // Saltamos órdenes con status_id = 5 que ya tienen folio
            if ($order->status_id == 5 && !empty($order->folio)) {
                continue;
            }

            // Encontramos el próximo índice disponible
            while (isset($used_indices[$current_index])) {
                $current_index++;
            }

            $folio = $base_folio . '-' . $current_index;

            // Verificación final por si acaso
            while (Order::where('folio', $folio)->where('id', '!=', $order->id)->exists()) {
                $current_index++;
                $folio = $base_folio . '-' . $current_index;
            }

            $order->update(['folio' => $folio]);
            $used_indices[$current_index] = true;
            $current_index++;
        }*/
    }

    public function destroy(Request $request, int $id)
    {
        $contract = Contract::find($id);
        if ($contract) {
            $orders = Order::where('contract_id', $id)->pluck('id');
            OrderService::whereIn('order_id', $orders)->delete();
            Order::where('contract_id', $id)->delete();
            ContractService::where('contract_id', $id)->delete();
            $contract->delete();
        }

        return redirect()->back();
    }

    private static function setFile($file, $id)
    {
        $file_name = $file->getClientOriginalExtension();
        $path = ContractController::$files_path . $id;
        $file->storeAs($path, $file_name);
        return $path . '/' . $file_name;
    }

    public function store_file(Request $request, string $customerID, int $type)
    {

        if ($request->file('file')) {
            $files = $request->file('file');
        } else {
            $error = 'No se encontraron los archivos';
        }
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $path = ContractController::setFile($files, $request->customer_id);
        $contractfile = new Contract_File();
        $contractfile->contract_id = $request->contract_id;
        $contractfile->path = $path;
        $contractfile->save();
        return redirect()->action([ContractController::class, 'index'], ['page' => 1]);
    }

    public function contract_downolad(string $id)
    {
        $file = Contract_File::find($id);
        if ($file) {
            return response()->download(storage_path('app/' . $file->path));
        }
    }

    public function renew(string $id)
    {
        $contract = Contract::find($id);
        $technicians = Technician::with('user')
            ->join('user as u', 'technician.user_id', '=', 'u.id')
            ->where('u.status_id', 2)
            ->orderBy('u.name', 'ASC')
            ->select('technician.*', 'u.name as user_name')
            ->get();

        $frequencies = ExecFrequency::all();
        $contracts = Contract::all();
        $intervals = $this->intervals;

        $configurations = [];
        $selected_services = [];
        $count_indexs = [];

        $contract_services = ContractService::where('contract_id', $contract->id)->get();

        foreach ($contract_services as $index => $cs) {

            if (!isset($count_indexs[$cs->service_id])) {
                $count_indexs[$cs->service_id] = 1;
            } else {
                $count_indexs[$cs->service_id]++;
            }

            $orders = Order::where('setting_id', $cs->id)->get();
            $configurations[] = [
                'config_id' => $count_indexs[$cs->service_id],
                'setting_id' => $cs->id,
                'service_id' => $cs->service_id,
                'frequency' => $cs->execfrequency->name,
                'frequency_id' => $cs->execution_frequency_id,
                'interval' => $this->intervals[$cs->interval],
                'interval_id' => $cs->interval,
                'days' => explode(',', json_decode($cs->days)[0] ?? ''),
                'dates' => array_map(function ($date) {
                    return $date . 'T00:00:00.000Z';
                }, $orders->pluck('programmed_date')->toArray())
            ];

            $selected_services[] = [
                'id' => $cs->service_id,
                'prefix' => $cs->service->prefix,
                'name' => $cs->service->name,
                'type' => $cs->service->serviceType->name,
                'line' => $cs->service->businessLine->name,
                'cost' => $cs->service->cost,
                'description' => $cs->service->description,
                #settings: [],
            ];
        }

        /*$customer = [
            'id' => $contract->customer_id,
            'name' => $contract->customer->name,
            'code' => $contract->customer->code,
            'address' => $contract->customer->address,
            'type' => $contract->customer->serviceType->name
        ];*/

        $new_dates = [
            Carbon::parse($contract->startdate)->addYear()->format('Y-m-d'),
            Carbon::parse($contract->enddate)->addYear()->format('Y-m-d')
        ];

        //dd($new_dates);
        $prefixes = ServicePrefix::pluck('name', 'id');
        $can_renew = true;

        return view(
            'contract.renew',
            compact(
                'contract',
                'frequencies',
                'technicians',
                'contracts',
                'intervals',
                'selected_services',
                'configurations',
                'new_dates',
                'can_renew',
                'prefixes',
            )
        );
    }
}
