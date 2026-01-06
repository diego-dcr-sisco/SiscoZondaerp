<?php

namespace App\Http\Controllers;

use App\Models\ContractService;
use App\Models\EvidencePhoto;
use App\Models\FloorplanArea;
use App\Models\WarehouseOrder;
use App\Models\Warehouse;
use App\Models\DevicePest;
use App\Models\DeviceProduct;
use App\Models\DeviceStates;
use App\Models\FloorPlans;
use App\Models\FloorplanVersion;
use App\Models\Order;
use App\Models\OrderIncidents;
use App\Models\Device;
use App\Models\OrderProduct;
use App\Models\OrderRecommendation;
use App\Models\OrderStatus;
use App\Models\MovementProduct;
use App\Models\PestCatalog;
use App\Models\ProductCatalog;
use App\Models\PropagateService;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\Service;

use App\Models\Lot;
use App\Models\ServiceType;
use App\Models\WarehouseProductOrder;
use App\PDF\MyPDF;

use Aws\EventBridge\EventBridgeEndpointMiddleware;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\DeviatesCastableAttributes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Dosage;
use App\Models\Metric;
use App\Models\OrderTechnician;
use App\Models\RotationPlan;
use App\Models\UserFile;
use App\Models\Recommendations;
use App\Models\Question;
use App\Models\ApplicationMethod;
use App\Models\ControlPoint;
use App\Models\AppearanceSetting;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

use App\Models\Technician;
use App\PDF\Certificate;
use App\Models\Customer;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rules\In;

class ReportController extends Controller
{

    private $file_answers_path = 'datas/json/answers.json';
    private $temp_bulk = 'temp/bulk_certificates/';

    private $recommendations = [
        'Mantener puertas, accesos cerrados, cuando la operación no lo requiera, para evitar el ingreso de organismos al interior.',
        'Limpieza constante, respetar programas existentes, para evitar acumulamientos de residuos que sean atrayentes de insectos rastreros, voladores y roedores.',
        'No mantener aguas superficiales o retenidas, encharcamientos que sean atrayentes o sirven para generación de plagas.',
        'Mantener áreas verdes, jardines y/o vegetación con el mantenimiento adecuado, evitar maleza alta que sea refugio de insectos.',
        'Sellar huecos, hendiduras y/o grietas en piso y paredes, que sirvan de refugio para insectos rastreros y/o roedores.',
        'Realizar mantenimiento a puertas, consiguiendo un sello hermético, evitando el ingreso de organismos (guardapolvos en condiciones, empalme de puertas).',
        'Colocación de malla mosquitera en ventanas, extractores, o zonas de ventilación, para evitar el ingreso de organismos rastreros, voladores, roedores y/o aves.',
        'Inspección de materia prima, materiales (tarimas, cajas), unidades de transporte, antes de ingresar a áreas de producción, evitando el arribo de plagas, omitiendo los controles establecidos.',
        'No realizar comportamientos y hábitos higiénicos que generen atrayentes de organismos, tales como ingerir alimentos en áreas inadecuadas, restos de comida, etc.',
    ];

    public function __construct()
    {
    }

    private function getOptions($id, $answers)
    {
        foreach ($answers as $answer) {
            if ($answer['id'] == $id) {
                return $answer['options'];
            }
        }
        return [];
    }

    public function autoreview(Request $request, int $orderId)
    {
        try {
            $autoreview_data = json_decode($request->input('autoreview_data'), true);
            $order = Order::find($orderId);

            foreach ($autoreview_data['control_points'] as $controlPoint) {
                // Acceder a los datos de cada control point
                $controlPointId = $controlPoint['control_point_id'];
                $answers = $controlPoint['answers'];
                $products = $controlPoint['products'];
                $pests = $controlPoint['pests'];
                $devices = $controlPoint['devices'];
                $observations = $controlPoint['observations'];
                $clear = $controlPoint['clear'];
                $questions = $controlPoint['questions'];

                $products_data = [];

                if ($clear['questions']) {
                    OrderIncidents::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                if ($clear['products']) {
                    DeviceProduct::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                if ($clear['pests']) {
                    DevicePest::where('order_id', $order->id)->whereIn('device_id', $devices)->delete();
                }

                // Procesar cada elemento
                foreach ($devices as $deviceId) {
                    $updated_incidents = [];
                    $updated_products = [];
                    $updated_pests = [];
                    $updated_questions = [];

                    foreach ($answers as $questionId => $answer) {
                        if (in_array($questionId, $questions)) {
                            $oi = OrderIncidents::updateOrCreate(
                                [
                                    'order_id' => $order->id,
                                    'question_id' => $questionId,
                                    'device_id' => $deviceId,
                                ],
                                [
                                    'answer' => $answer
                                ]
                            );

                            $updated_questions[] = $questionId;
                            $updated_incidents[] = $oi->id;
                        }
                    }

                    foreach ($products as $product) {
                        $productId = $product['product_id'];
                        $amount = $product['amount'];
                        $lotId = $product['lot_id'];
                        $method_id = $product['application_method_id'];

                        $dp = DeviceProduct::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'device_id' => $deviceId,
                                'product_id' => $productId,
                            ],
                            [
                                'application_method_id' => $method_id != "" ? $method_id : null,
                                'lot_id' => $lotId != "" ? $lotId : null,
                                'quantity' => $amount != "" ? $amount : 0,
                                'possible_lot' => null
                            ]
                        );

                        $updated_products[] = $dp->id;
                    }

                    foreach ($pests as $pest) {
                        $pestId = $pest['pest_id'];
                        $count = $pest['count'];

                        $dp = DevicePest::updateOrCreate(
                            [
                                'order_id' => $orderId,
                                'device_id' => $deviceId,
                                'pest_id' => $pestId,
                            ],
                            [
                                'total' => $count,
                            ]
                        );

                        $updated_pests[] = $dp->id;
                    }

                    $ds = DeviceStates::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'device_id' => $deviceId
                        ],
                        [
                            'is_checked' => true,
                        ]
                    );

                    if ($clear['observations']) {
                        $ds->observations = null;
                        $ds->save();
                    } else {
                        if ($observations != "") {
                            $ds->observations = $observations;
                            $ds->save();
                        }
                    }

                    OrderIncidents::where('order_id', $order->id)->where('device_id', $deviceId)->where('question_id', $updated_questions)->whereNotIn('id', $updated_incidents)->delete();
                    //DeviceProduct::where('order_id', $order->id)->where('device_id', $deviceId)->whereNotIn('id', $updated_products)->delete();
                    //DevicePest::where('order_id', $order->id)->where('device_id', $deviceId)->whereNotIn('id', $updated_pests)->delete();
                }
            }

            $dps = DeviceProduct::where('order_id', $order->id)->get();
            $groupedProducts = $dps->groupBy('product_id');

            foreach ($groupedProducts as $product_id => $products) {
                $service = $order->services()->first();
                $totalAmount = $products->sum('quantity');
                $firstProduct = $products->first();
                $products_data[] = [
                    'product_id' => $product_id,
                    'service_id' => $service->id ?? null,
                    'lot_id' => $firstProduct->lot_id ?? null,
                    'app_method_id' => $firstProduct->application_method_id,
                    'amount' => $totalAmount,
                ];
            }

            $user = Auth::user();
            $technician = $order->closed_by ? Technician::where('user_id', $order->closed_by)->first() : null;
            $this->handleStock($order, $products_data, $technician, $user);
            $this->handleStock($order, $products_data, $technician, $user);


            return response()->json([
                'success' => true,
                'message' => 'Autorevisión guardada correctamente',
                'data' => $autoreview_data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en autoreview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la autorevisión: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleStock($order, $products_data, $technician, $user)
    {
        $updated_products = [];
        $updated_lots = [];
        $updated_order_products = [];

        $updated_wos = [];

        $warehouse = $technician ? Warehouse::where('technician_id', $technician->id)->first() : null;
        foreach ($products_data as $product_data) {
            $wm = null;
            $product = ProductCatalog::find($product_data['product_id']);
            $op = OrderProduct::updateOrCreate([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'lot_id' => $product_data['lot_id'] ?? null,
            ], [
                'service_id' => $product_data['service_id'],
                'metric_id' => $product->metric_id,
                'application_method_id' => $product_data['app_method_id'] ?? null,
                'amount' => $product_data['amount'],
                'dosage' => $product->dosage
            ]);

            $updated_order_products[] = $op->id;

            if ($warehouse) {
                $wm = WarehouseMovement::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'destination_warehouse_id' => null,
                        'movement_id' => 8,
                        'observations' => 'Movimiento realizado en la order #' . $order->folio . ' | ID: ' . $order->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'date' => now(),
                        'time' => now(),
                        'updated_at' => now()
                    ]
                );

                $mp = MovementProduct::updateOrCreate([
                    'warehouse_movement_id' => $wm->id,
                    'movement_id' => 8,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,

                ], [
                    'lot_id' => $product_data['lot_id'] ?? null,
                    'amount' => $product_data['amount'],
                ]);

                $updated_products[] = $mp->product_id;
                $updated_lots[] = $mp->lot_id;
            }

            $wo = WarehouseOrder::updateOrCreate([
                'movement_id' => 8,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'product_id' => $product_data['product_id'],
            ], [
                'warehouse_id' => $warehouse->id ?? null,
                'warehouse_movement_id' => $wm->id ?? null,
                'lot_id' => $product_data['lot_id'] ?? null,
                'amount' => $product_data['amount'],
            ]);

            $updated_wos[] = $wo->id;
        }

        if (count($updated_products) > 0 || count($updated_lots) > 0) {
            MovementProduct::where('warehouse_id', $warehouse->id)->where('movement_id', 8)
                ->whereNotIn('product_id', $updated_products)
                ->whereNotIn('lot_id', $updated_lots)
                ->delete();
        }

        OrderProduct::where('order_id', $order->id)->whereNotIn('id', $updated_order_products)->delete();
        WarehouseOrder::where('order_id', $order->id)->whereNotIn('id', $updated_wos)->delete();
    }

    private function getDevicesByVersion($order_id, $version = null)
    {
        $found_devices = [];
        $f_versions = [];
        $order = Order::find($order_id);
        $floorplans = FloorPlans::where('customer_id', $order->customer_id)
            ->whereIn('service_id', $order->services()->pluck('service.id'))
            ->get();

        if ($floorplans->isNotEmpty()) {
            foreach ($floorplans as $floorplan) {
                $versions = FloorplanVersion::where('floorplan_id', $floorplan->id)->get();
                $version = $versions->where('updated_at', '<=', $order->programmed_date)->last();
                if ($version) {
                    $f_versions[] = [
                        'floorplan_id' => $floorplan->id,
                        'version' => $version->version,
                    ];
                } else {
                    $f_versions[] = [
                        'floorplan_id' => $floorplan->id,
                        'version' => $versions->last()?->version,
                    ];
                }
            }

            $found_devices = [];
            foreach ($f_versions as $fv) {
                $devices = Device::where('floorplan_id', $fv['floorplan_id'])
                    ->where('version', $fv['version'])
                    ->pluck('id')
                    ->toArray();
                $found_devices = array_merge($found_devices, $devices);
            }
        }

        return $found_devices;
    }

    public function create(string $id)
    {
        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);
        $autoreview_data = [];
        $devices_data = [];

        $devices_1 = [];
        $devices_2 = [];

        $order = Order::find($id);

        if (!$order) {
            return back()->withErrors(['error' => 'Order not found.']);
        }

        $incidents = OrderIncidents::where('order_id', $order->id)->get();

        if ($order->status_id == 5 && $incidents->isNotEmpty()) {
            $devices_incidents = $incidents->pluck('device_id')->toArray();
            $devices = Device::whereIn('id', $devices_incidents)->orderBy('nplan', 'ASC')->get();

            /*$count = array_count_values($devices_1->pluck('version')->toArray());
            $maxsum = max($count);
            $version = array_search($maxsum, $count);

            $found_devices = $this->getDevicesByVersion($id);
            //dd($found_devices);
            //$found_devices = array_merge($devices_1->pluck('id')->toArray(), array_diff($found_devices, $devices_1->pluck('id')->toArray()));
            $devices = Device::whereIn('id', $found_devices)->orderBy('nplan', 'ASC')->get();*/

        } else {
            $found_devices = $this->getDevicesByVersion($id);
            $devices = Device::whereIn('id', $found_devices)->orderBy('nplan', 'ASC')->get();
        }

        $control_points = ControlPoint::whereIn('id', $devices->pluck('type_control_point_id')->unique())->get();

        foreach ($control_points as $control_point) {
            $questions_data = [];
            $questions = $control_point->questions()->get();

            foreach ($questions as $question) {
                $questions_data[] = [
                    'id' => $question->id,
                    'question' => $question->question,
                    'answer_default' => $question->answer_default,
                    'answers' => $this->getOptions($question->question_option_id, $answers)
                ];
            }

            $autoreview_data[] = [
                'control_point_id' => $control_point->id,
                'control_point_name' => $control_point->name,
                'questions' => $questions_data,
                'devices' => $devices->where('type_control_point_id', $control_point->id)->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'code' => $device->code,
                        'name' => $device->controlPoint->name
                    ];
                })->values()->toArray(),
                'pests' => PestCatalog::all()->map(function ($pest) {
                    return [
                        'id' => $pest->id,
                        'name' => strtoupper($pest->name),
                        'category' => $pest->pestCategory->category
                    ];
                })->toArray(),
                'products' => ProductCatalog::all()->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => strtoupper($product->name),
                        'lots' => $product->lots()->get()->map(function ($lot) {
                            return [
                                'id' => $lot->id,
                                'registration_number' => $lot->registration_number
                            ];
                        })->toArray(),
                    ];
                })->toArray()
            ];
        }

        //dd($devices->pluck('id'));
        foreach ($devices as $device) {
            $questions_data = [];
            $questions = $device->controlPoint->questions()->get();

            foreach ($questions as $question) {
                $questions_data[] = [
                    'id' => $question->id,
                    'question' => $question->question,
                    'answer' => $device->incident($order->id, $question->id)->answer ?? null,
                    'answer_default' => $question->answer_default,
                    'answers' => $this->getOptions($question->question_option_id, $answers)
                ];
            }

            $device_states = $device?->states($order->id)->first() ?? null;

            $devices_data[] = [
                'id' => $device->id,
                'nplan' => $device->nplan,
                'code' => $device->code,
                'service' => [
                    'id' => $device->floorplan->service_id,
                    'name' => $device->floorplan->service->name
                ],
                'floorplan' => [
                    'id' => $device->floorplan_id,
                    'name' => $device->floorplan->filename ?? '-',
                ],
                'control_point' => [
                    'id' => $device->controlPoint->id,
                    'name' => $device->controlPoint->name
                ],
                'application_area' => [
                    'id' => $device->application_area_id,
                    'name' => $device->applicationArea->name ?? '-'
                ],
                'questions' => $questions_data,
                'pests' => $device->pests($order->id)->map(function ($dp) {
                    return [
                        'key' => (string) time(),
                        'id' => $dp->pest_id,
                        //'pest_id' => $dp->pest_id,
                        'name' => $dp->pest->name,
                        'device_id' => $dp->device_id,
                        'total' => $dp->total,
                    ];
                })->toArray() ?? null,

                'products' => $device->products($order->id)->map(function ($dp) {
                    return [
                        'key' => (string) time(),
                        'id' => $dp->product_id,
                        'order_id' => $dp->order_id,
                        'device_id' => $dp->device_id,
                        'application_method_id' => $dp->application_method_id,
                        //'product_id' => $dp->product_id,
                        'lot_id' => $dp->lot_id,
                        'name' => $dp->product->name ?? null,
                        'quantity' => $dp->quantity,
                        'metric' => $dp->product->metric->value
                    ];
                })->toArray() ?? null,
                'states' => [
                    'order_id' => $order->id,
                    'device_id' => $device->id,
                    'is_scanned' => $device_states->is_scanned ?? false,
                    //'is_checked' => (($device_states?->is_checked ?? false) || count($questions_data) > 0),
                    'is_checked' => $device_states?->is_checked ?? false,
                    'observations' => $device_states->observations ?? null,
                    'device_image' => $device_states->device_image ?? null
                ]
            ];
        }

        //dd($devices_data);

        $products = ProductCatalog::with([
            'lots' => function ($query) {
                $query->select(['id', 'product_id', 'registration_number']);
            },
            'applicationMethods' => function ($query) {
                $query->select(['application_method.id', 'application_method.name']);
            }
        ])
            ->join('metric', 'product_catalog.metric_id', '=', 'metric.id')
            ->select([
                'product_catalog.id',
                'product_catalog.name',
                'product_catalog.updated_at',
                'metric.value as metric'
            ])
            ->orderBy('product_catalog.name')
            ->get();

        $pests = PestCatalog::select(['pest_catalog.id', 'pest_catalog.name', 'pest_catalog.updated_at'])
            ->orderBy('pest_catalog.name')
            ->get();

        $application_methods = ApplicationMethod::select(['application_method.id', 'application_method.name', 'application_method.updated_at'])
            ->orderBy('application_method.name')
            ->get();

        $order_status = OrderStatus::all();
        $user_technicians = User::where('status_id', 2)
            ->where(function ($query) {
                $query->where('role_id', 3)
                    ->orWhere('work_department_id', 8);
            })
            ->orderBy('name')
            ->get();
        $service_types = ServiceType::all();
        $metrics = Metric::all();
        $lots = Lot::all();

        $devices = $devices_data;
        $recommendations = $this->recommendations;

        $navigation = [
            'Orden de servicio' => route('order.edit', ['id' => $order->id]),
            'Reporte' => route('report.review', ['id' => $order->id]),
            'Seguimientos' => route('tracking.create.order', ['id' => $order->id]),
        ];


        return view('report.create', compact(
            'order',
            'autoreview_data',
            'devices',
            'products',
            'pests',
            'application_methods',
            'order_status',
            'user_technicians',
            'service_types',
            'recommendations',
            'metrics',
            'lots',
            'navigation'
        ));
    }

    public function setIncident(Request $request, int $orderId)
    {
        try {
            $review = json_decode($request->input('review'), true);
            $order = Order::findOrFail($orderId);

            DB::beginTransaction();

            $device_id = $review['device_id'];
            $questions = $review['questions'];
            $pests = $review['pests'];
            $products = $review['products'];
            $observations = $review['observations'] ?? null;

            // Arrays para trackear registros actualizados
            $updated_incidents = [];
            $updated_products = [];
            $updated_pests = [];
            $products_data = [];

            // Procesar preguntas (incidentes)
            foreach ($questions as $question) {
                $incident = OrderIncidents::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'question_id' => $question['id'],
                        'device_id' => $device_id
                    ],
                    ['answer' => $question['answer']]
                );
                $updated_incidents[] = $incident->id;
            }

            // Procesar plagas
            foreach ($pests as $pest) {
                $dp = DevicePest::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'device_id' => $device_id,
                        'pest_id' => $pest['id'],
                    ],
                    [
                        'total' => $pest['quantity'],
                    ]
                );
                $updated_pests[] = $dp->id;
            }

            // Procesar productos
            foreach ($products as $product) {
                $dp = DeviceProduct::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'device_id' => $device_id,
                        'product_id' => $product['id'],
                    ],
                    [
                        'application_method_id' => $product['application_method_id'],
                        'lot_id' => $product['lot_id'],
                        'quantity' => $product['quantity'] ?? 0,
                        'possible_lot' => null
                    ]
                );
                $updated_products[] = $dp->id;
            }

            DeviceStates::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'device_id' => $device_id
                ],
                [
                    'is_checked' => true,
                    'observations' => $observations,
                ]
            );

            // Opcional: Eliminar registros que no están en los arrays actualizados
            // (si necesitas sincronización completa)
            $this->cleanOldRecords($order->id, $device_id, $updated_incidents, $updated_pests, $updated_products);

            $dps = DeviceProduct::where('order_id', $order->id)->get();
            $groupedProducts = $dps->groupBy('product_id');

            foreach ($groupedProducts as $product_id => $products) {
                $service = $order->services()->first();
                $totalAmount = $products->sum('quantity');
                $firstProduct = $products->first();
                $products_data[] = [
                    'product_id' => $product_id,
                    'service_id' => $service->id ?? null,
                    'lot_id' => $firstProduct->lot_id,
                    'app_method_id' => $firstProduct->application_method_id,
                    'amount' => $totalAmount,
                ];
            }

            $user = Auth::user();
            $technician = $order->closed_by ? Technician::where('user_id', $order->closed_by)->first() : null;
            $this->handleStock($order, $products_data, $technician, $user);

            $order_products = OrderProduct::where('order_id', $order->id)->get();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Incidentes actualizados correctamente.',
                'data' => $review,
                'order_products' => $order_products->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'product' => [
                            'id' => $p->product_id,
                            'name' => $p->product->name
                        ],
                        'service' => [
                            'id' => $p->service_id ?? null,
                            'name' => $p->service->name ?? '-'
                        ],
                        'application_method' => [
                            'id' => $p->application_method_id ?? null,
                            'name' => $p->appMethod->name ?? '-'
                        ],
                        'lot' => [
                            'id' => $p->lot_id ?? null,
                            'name' => $p->lot->registration_number ?? '-'
                        ],
                        'metric' => [
                            'id' => $p->metric_id,
                            'value' => $p->metric->value
                        ],
                        'amount' => $p->amount,
                        'dosage' => $p->dosage,
                        'possible_lot' => $p->possible_lot,
                        'data' => $p
                    ];
                })
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada.'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en setIncident - Order: {$orderId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método opcional para limpiar registros antiguos
    protected function cleanOldRecords($orderId, $deviceId, $updatedIncidents, $updatedPests, $updatedProducts)
    {
        // Ejemplo para incidentes (preguntas)
        OrderIncidents::where('order_id', $orderId)
            ->where('device_id', $deviceId)
            ->whereNotIn('id', $updatedIncidents)
            ->delete();

        // Ejemplo para plagas
        DevicePest::where('order_id', $orderId)
            ->where('device_id', $deviceId)
            ->whereNotIn('id', $updatedPests)
            ->delete();

        // Ejemplo para productos
        DeviceProduct::where('order_id', $orderId)
            ->where('device_id', $deviceId)
            ->whereNotIn('id', $updatedProducts)
            ->delete();
    }

    function limpiarHTMLParaDOMPDF($html)
    {
        // 1. Eliminar estilos inline innecesarios y normalizar
        $html = preg_replace('/style="[^"]*font-size:\s*11px[^"]*"/i', 'class="texto-pequeno"', $html);
        $html = preg_replace('/style="[^"]*font-size:\s*12px[^"]*"/i', 'class="texto-mediano"', $html);
        $html = preg_replace('/style="[^"]*font-size:\s*14px[^"]*"/i', 'class="texto-grande"', $html);

        // 2. Eliminar estilos de línea-height (DOMPDF maneja mejor el espaciado con CSS)
        $html = preg_replace('/style="line-height:[^;"]*;/i', '', $html);
        $html = preg_replace('/style="[^"]*line-height:[^;"]*[;"]/i', '', $html);

        // 3. Normalizar negritas (evitar anidamientos redundantes)
        $html = preg_replace('/<b>\s*<b>/', '<b>', $html);
        $html = preg_replace('/<\/b>\s*<\/b>/', '</b>', $html);

        // 4. Limpiar espacios innecesarios (especialmente &nbsp;)
        $html = str_replace('&nbsp;', ' ', $html);
        $html = preg_replace('/\s+/', ' ', $html);

        // 5. Optimizar párrafos y listas
        $html = preg_replace('/<p[^>]*>\s*<\/p>/', '', $html); // Eliminar párrafos vacíos
        $html = preg_replace('/<span[^>]*>\s*<\/span>/', '', $html); // Eliminar spans vacíos

        // 6. Corregir estructura de listas (DOMPDF es sensible a esto)
        $html = preg_replace('/<ul>\s*<br\s*\/?>/', '<ul>', $html);
        $html = preg_replace('/<br\s*\/?>\s*<\/ul>/', '</ul>', $html);

        // 7. Eliminar saltos de línea innecesarios
        $html = str_replace(["\r", "\n"], '', $html);

        return $html;
    }

    // CSS recomendado para incluir en tu documento
    function obtenerCSSOptimizado()
    {
        return '
        <style>
            .texto-pequeno { font-size: 11px; }
            .texto-mediano { font-size: 12px; }
            .texto-grande { font-size: 14px; }
            p, ul { margin: 5px 0; line-height: 1.2; }
            li { margin-left: 20px; }
            b, strong { font-weight: bold; }
        </style>
    ';
    }

    public function generate(Request $request, string $orderId)
    {
        $propagate = json_decode($request->input('summary_services'));

        $order = Order::find($orderId);
        $notes = $request->notes;
        $order->update(['notes' => $notes]);

        if (!$order) {
            return back()->withErrors(['error' => 'Order not found.']);
        }

        foreach ($order->services as $service) {
            $query_orders = Order::where('contract_id', $order->contract_id)
                ->where('setting_id', $order->setting_id);

            $orders = $query_orders->get();

            foreach ($orders as $ord) {
                $recs_data = $propagate[$service->id]->recs;
                if (is_array($recs_data)) {
                    if (count($recs_data) > 0) {
                        $updated_recs = [];

                        foreach ($recs_data as $rec_id) {
                            OrderRecommendation::updateOrCreate([
                                'order_id' => $ord->id,
                                'service_id' => $service->id,
                                'recommendation_id' => $rec_id,
                            ], [
                                'recommendation_text' => null,
                            ]);

                            $updated_recs[] = $rec_id;
                        }

                        OrderRecommendation::where('order_id', $ord->id)
                            ->where('service_id', $service->id)
                            ->whereNotIn('recommendation_id', $updated_recs)
                            ->delete();
                    } else {
                        OrderRecommendation::where('order_id', $ord->id)
                            //->where('service_id', $service->id)
                            ->delete();
                    }
                } else {
                    $has_text = $recs_data != null && $recs_data != '' && $recs_data != '<p><br></p>';
                    OrderRecommendation::updateOrCreate([
                        'order_id' => $order->id,
                        'service_id' => $service->id
                    ], [
                        'recommendation_id' => null,
                        'recommendation_text' => $has_text ? $recs_data : '<p><strong>ANTES DE LA APLICACIÓN QUÍMICA</strong></p><ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Identificar la plaga a controlar.</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>No debe encontrarse personal en el área.</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>No debe de haber materia prima expuesta.</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Asegurar que la aplicación no afecte el proceso, producción o a terceros.</li></ol><p><br></p><p><strong>DURANTE DE LA APLICACIÓN QUÍMICA</strong></p><ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>En el área solo debe de encontrarse el técnico aplicador</li></ol><p><br></p><p><strong>DESPUÉS DE LA APLICACIÓN QUÍMICA</strong></p><ol><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Respetar el tiempo de reentrada conforme a la etiqueta del producto a utilizar.</li><li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Realizar recolección de plaga o limpieza necesaria al tipo de área.</li></ol>',
                    ]);
                }
            }
        }

        /*if ($order->technicians()->count() == 1 && $order->closed_by == null) {
            $order->update(['closed_by' => $order->technicians()->first()->user_id]);
        } else {
            if ($order->closed_by != null) {
                $technician = Technician::where('user_id', $order->closed_by)->first();
                OrderTechnician::updateOrCreate(
                    ['order_id' => $order->id],
                    ['technician_id' => $technician->id]
                );
                OrderTechnician::where('order_id', $order->id)->where('technician_id', '!=', $technician->id)->delete();
            }
        }*/

        return redirect()->route('report.print', ['orderId' => $orderId]);
    }

    public function propagate(string $orderId, string $serviceId, string $productId)
    {
        $order = Order::find($orderId);
        $service = Service::find($serviceId);
        $product = ProductCatalog::find($productId);

        $order_product = OrderProduct::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->where('service_id', $service->id)->first();

        $orders = Order::where('contract_id', $order->contract_id)->where('status_id', '<', 5)->get();

        foreach ($orders as $order) {
            OrderProduct::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'product_id' => $product->id,
                    'application_method_id' => $order_product->application_method_id,
                    'lot_id' => $order_product->lot_id,
                ],
                [
                    'amount' => $order_product->amount,
                    'dosage' => $order_product->dosage,
                ]
            );
        }

        return back();
    }

    public function searchProduct(Request $request)
    {
        try {
            $search = $request->input('search');
            if (empty($search)) {
                return response()->json(['error' => 'Search term is required.'], 400);
            }

            $searchTerm = '%' . $search . '%';

            $products = ProductCatalog::where('name', 'LIKE', $searchTerm)
                ->select('id', 'name', 'dosage')
                ->get();

            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found.'], 404);
            }

            $lots = Lot::whereIn('product_id', $products->pluck('id')->toArray())->where('amount', '>', 0)->get();

            $data = [
                'products' => $products,
                'lots' => $lots,
            ];

            return response()->json(['data' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while searching for products.', 'details' => $e->getMessage()], 500);
        }
    }

    public function setProduct(Request $request, string $orderId)
    {
        $products_data = [];
        $data = $request->all();
        $order = Order::find($orderId);
        $op_id = $data['op_id'];

        if (!$op_id) {
            //dd('1');
            $order_product = OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $data['product_id'],
                'service_id' => $data['service_id'],
                'metric_id' => $data['metric_id'] ?? null,
                'application_method_id' => $data['application_method_id'] ?? null,
                'lot_id' => $data['lot_id'],
                'amount' => $data['amount'],
                'dosage' => $data['dosage']
            ]);

        } else {
            $order_product = OrderProduct::find($op_id);
            $order_product->update([
                'product_id' => $data['product_id'],
                'service_id' => $data['service_id'],
                'metric_id' => $data['metric_id'] ?? null,
                'application_method_id' => $data['application_method_id'] ?? null,
                'lot_id' => $data['lot_id'] ?? null,
                'amount' => $data['amount'],
                'dosage' => $data['dosage']
            ]);

        }

        $dps = DeviceProduct::where('order_id', $order->id)->get();
        $groupedProducts = $dps->groupBy('product_id');

        foreach ($groupedProducts as $product_id => $products) {
            $service = $order->services()->first();
            $totalAmount = $products->sum('quantity');
            $firstProduct = $products->first();
            $products_data[] = [
                'product_id' => $product_id,
                'service_id' => $service->id ?? null,
                'lot_id' => $firstProduct->lot_id,
                'app_method_id' => $firstProduct->application_method_id,
                'amount' => $totalAmount,
            ];
        }

        $ops = OrderProduct::where('order_id', $order->id)->get();
        $groupedProducts = $ops->groupBy('product_id');

        foreach ($groupedProducts as $product_id => $products) {
            $service = $order->services()->first();
            $totalAmount = $products->sum('amount');
            $firstProduct = $products->first();
            $products_data[] = [
                'product_id' => $product_id,
                'service_id' => $service->id ?? null,
                'lot_id' => $firstProduct?->lot_id,
                'app_method_id' => $firstProduct->application_method_id,
                'amount' => $totalAmount,
            ];
        }

        $user = Auth::user();
        $technician = $order->closed_by ? Technician::where('user_id', $order->closed_by)->first() : null;
        $this->handleStock($order, $products_data, $technician, $user);
        return redirect()->route('report.review', ['id' => $order->id]);
    }

    /*protected function createNewOrderProduct($orderId, $data, $lot = null)
    {
        $orderProduct = OrderProduct::create([
            'order_id' => $orderId,
            'product_id' => $data['product_id'],
            'service_id' => $data['service_id'],
            'metric_id' => $data['metric_id'] ?? null,
            'application_method_id' => $data['application_method_id'] ?? null,
            'lot_id' => $data['lot_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'dosage' => $data['dosage'] ?? 0
        ]);

        $this->updateWarehouse($orderId, $data['product_id'], $data['amount'] ?? 0, $lot);

        return back()->with('success', 'Producto agregado a la orden');
    }

    protected function updateWarehouse($orderId, $productId, $amount, $lot = null)
    {
        // Validar que tenemos un warehouse_id válido
        $warehouseId = $lot?->warehouse?->id;

        if (empty($warehouseId)) {
            //throw new \InvalidArgumentException("El warehouse_id no puede ser nulo. Verifica el lote proporcionado.");
            return;
        }

        $movement = WarehouseProductOrder::firstOrNew([
            'order_id' => $orderId,
            'product_id' => $productId
        ]);

        $movement->amount = $amount;
        $movement->lot_id = $lot?->id;
        $movement->warehouse_id = $warehouseId;
        $movement->save();

    }*/

    public function destroyProduct(string $dataId)
    {

        $order_product = OrderProduct::find($dataId);
        $order = Order::find($order_product->order_id);

        $order_product->delete();

        WarehouseOrder::where('order_id', $order_product->order_id)
            ->where('product_id', $order_product->product_id)
            ->where('lot_id', $order_product->lot_id)
            ->delete();

        $technician = $order->closed_by ? Technician::where('user_id', $order->closed_by)->first() : null;
        $warehouse = $technician ? Warehouse::where('technician_id', $technician->id)->first() : null;

        if ($warehouse) {

            $wm = WarehouseMovement::where('warehouse_id', $warehouse->id)
                ->where('movement_id', 8)
                ->where('destination_warehouse_id', null)
                ->where('observations', 'Movimiento realizado en la order #' . $order->folio . ' | ID: ' . $order->id)
                ->first();

            if ($wm) {
                $mp = MovementProduct::where('warehouse_movement_id', $wm->id)
                    ->where('movement_id', 8)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_id', $order_product->product_id)
                    ->where('lot_id', $order_product->lot_id)
                    ->first();

                if ($mp) {
                    $mp->delete();
                }
            }
        }

        return back();
    }

    public function print(string $orderId)
    {
        //$tempDir = storage_path('app/temp/signatures');

        $data = [];
        $certificate = new Certificate($orderId);
        $certificate->order();
        $certificate->branch();
        $certificate->customer();
        $certificate->technician();
        $certificate->services();
        $certificate->products();
        $certificate->devices();
        $certificate->notes();
        $certificate->recommendations();
        $certificate->photoEvidences();
        $data = $certificate->getData();

        // Obtener la configuración de apariencia
        $appearance = AppearanceSetting::first();

        // Si no existe, crear una instancia con valores por defecto
        if (!$appearance) {
            $appearance = new AppearanceSetting();
        }

        // Agregar los colores y la ruta del logo a los datos que se pasan a la vista
        $data['primaryColor'] = $appearance->primary_color;
        $data['secondaryColor'] = $appearance->secondary_color;
        $data['logoPath'] = $appearance->logo_path ?: 'images/logo_reporte.png';
        $data['watermarkPath'] = $appearance->watermark_path ?: 'images/watermark.png';
        $data['watermarkOpacity'] = $appearance->watermark_opacity ?: 0.1;

        //dd($data);
        //Si son texto plano formatear las notas antes de generar el PDF
        if (isset($data['notes'])) {
            $notesContent = $data['notes'];
            if ($this->isPlainText($notesContent)) {
                $data['notes'] = $this->formatPlainTextToHtml($notesContent);
            }
        } else {
            $data['notes'] = 'Sin notas';
        }

        $pdf = Pdf::loadView('report.pdf.certificate', $data)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            //'dpi' => 150,
            'defaultFont' => $data['font_family'] ?? 'Arial'
        ]);

        /*register_shutdown_function(function () use ($tempDir) {
            if (File::exists($tempDir)) {
                File::cleanDirectory($tempDir);
            }
        });*/

        return $pdf->stream($data['filename']);
    }

    public function bulkPrint(Request $request)
    {
        $zip = null;

        try {
            // Configuración inicial
            $timer = date('Y-m-d H:i:s');

            if (!Storage::exists($this->temp_bulk)) {
                Storage::makeDirectory($this->temp_bulk);
            }

            $selected_orders = json_decode($request->input('selectedOrders', '[]'));
            if (empty($selected_orders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionaron órdenes para procesar'
                ], 400);
            }

            foreach ($selected_orders as $order_id) {
                try {
                    $certificate = new Certificate($order_id);
                    $certificate->order();
                    $certificate->branch();
                    $certificate->customer();
                    $certificate->technician();
                    $certificate->services();
                    $certificate->products();
                    $certificate->devices();
                    $certificate->notes();
                    $certificate->recommendations();

                    $data = $certificate->getData();
                    $filename = $data['filename'] ?? 'certificado_' . $order_id . '.pdf';
                    $tempPath = $this->temp_bulk . $timer . '/' . $filename;

                    // Generar PDF
                    $pdf = Pdf::loadView('report.pdf.certificate', $data)
                        ->setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                            //'dpi' => 150,
                            'defaultFont' => $data['font_family'] ?? 'Arial'
                        ]);
                    Storage::put($tempPath, $pdf->output());

                } catch (\Exception $e) {
                    Log::error("Error generando certificado para orden $order_id: " . $e->getMessage());
                    continue;
                }
            }

            $zip_name = 'certificados.zip';
            $zip_path = Storage::path($this->temp_bulk . $timer . '/' . $zip_name);
            $folder_path = Storage::path($this->temp_bulk . $timer);

            $zip = new ZipArchive;
            if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $files = File::allFiles($folder_path);
                foreach ($files as $file) {
                    $relativePath = substr($file->getPathname(), strlen($folder_path) + 1);
                    $zip->addFile($file->getPathname(), $relativePath);
                }
                $zip->close();
            } else {
                return back()->with('error', 'No se pudo crear el archivo ZIP');
            }

            return response()->json([
                'success' => true,
                'download_url' => route('report.bulk.download', ['timer' => $timer]),
                'delete_url' => route('report.bulk.delete', ['timer' => $timer])
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error en bulkPrint: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadBulk($timer)
    {
        $zip_name = 'certificados.zip';
        $storage_relative_path = $this->temp_bulk . $timer;
        $zip_relative_path = $storage_relative_path . '/' . $zip_name;

        // Verificar que el archivo ZIP existe
        if (!Storage::exists($zip_relative_path)) {
            return back()->with('error', 'El archivo ZIP no existe o fue eliminado');
        }

        // Obtener rutas completas
        $file_path = Storage::path($zip_relative_path);

        // Configurar headers
        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zip_name . '"',
            'Content-Length' => Storage::size($zip_relative_path),
        ];

        $response = Response::download($file_path, $zip_name, $headers)
            ->deleteFileAfterSend(true);

        register_shutdown_function(function () use ($storage_relative_path) {
            try {
                if (Storage::exists($storage_relative_path)) {
                    Storage::deleteDirectory($storage_relative_path);
                }
            } catch (\Exception $e) {
                Log::error("Cleanup failed: " . $e->getMessage());
            }
        });

        return $response;
    }

    public function deleteBulk($timer)
    {
        try {
            $path = $this->temp_bulk . $timer;
            if (Storage::exists($path)) {
                Storage::deleteDirectory($path);
            }
            return back();
        } catch (\Exception $e) {
            Log::error("Error en deleteBulk: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el contenido temporal: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDevices(Request $request)
    {
        $devices_data = [];
        $floorplan_id = $request->input('floorplan_id');
        $version = $request->input('version');

        $floorplan = FloorPlans::find($floorplan_id);

        $devices = Device::where('floorplan_id', $floorplan_id)
            ->where('version', $version)
            ->get();

        foreach ($devices as $device) {
            $devices_data[] = [
                'id' => $device->id,
                'name' => $device->code,
                'type' => $device->type_control_point_id,
                'type_name' => $device->controlPoint->name,
                //'pests' => $device->pests($request->input('order_id'))->select('pest_id', 'total')->toArray(),
                //'products' => $device->products($request->input('order_id'))->select('product_id', 'application_method_id', 'lot_id', 'quantity')->toArray(),
                //'is_product_changed' => $device->states($request->input('order_id'))->is_product_changed,
                //'is_device_changed' => $device->states($request->input('order_id'))->is_device_changed,
                //'is_scanned' => $device->states($request->input('order_id'))->is_scanned,
            ];
        }

        $data = [
            'service_id' => $floorplan->service_id,
            'service_name' => $floorplan->service->name,
            'devices' => $devices_data,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->input('order'), true);

        try {
            // Log para debugging en producción
            Log::info('updateOrder called', ['data' => $data, 'user_id' => Auth::id()]);

            // Validar que tenemos un ID de orden
            if (empty($data['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de orden requerido'
                ], 422);
            }

            // Validar firma solo si no es null y no está vacía
            if (!empty($data['signature_base64']) && !preg_match('/^data:image\/(jpeg|png);base64,/', $data['signature_base64'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de firma no válido'
                ], 422);
            }

            $order = Order::findOrFail($data['id']);

            $updated_order = [
                'programmed_date' => $data['programmed_date'] ?? $order->programmed_date,
                'completed_date' => !empty($data['completed_date']) ? $data['completed_date'] : null,
                'start_time' => $data['start_time'] ?? $order->start_time,
                'end_time' => !empty($data['end_time']) ? $data['end_time'] : null,
                'status_id' => $data['status'] ?? $order->status_id,
                'signature_name' => !empty($data['signed_by']) ? $data['signed_by'] : null,
                'customer_signature' => $data['signature_base64'] ?? null,
            ];

            // Manejar closed_by de manera segura
            if (isset($data['status']) && $data['status'] == 1) {
                $updated_order['closed_by'] = null;
            } else {
                // Solo actualizar closed_by si viene en el request y no está vacío
                if (isset($data['closed_by']) && $data['closed_by'] !== '' && $data['closed_by'] !== null) {
                    $updated_order['closed_by'] = (int)$data['closed_by'];
                }
            }

            $order->update($updated_order);

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizada correctamente',
                'order' => $order
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::error('Order not found in updateOrder', ['data' => $data, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in updateOrder', ['data' => $data, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la orden: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCustomer(Request $request)
    {
        $data = json_decode($request->input('customer'), true);

        try {
            $updated_customer = [
                'name' => $data['name'],
                'email' => $data['email'],
                'address' => $data['address'],
                'rfc' => $data['rfc'],
            ];

            if (!empty($data['id'])) {
                $customer = Customer::findOrFail($data['id']);
                $customer->update($updated_customer);
                $message = 'Customer updated successfully';
            } else {
                $customer = Customer::create($updated_customer);
                $message = 'Customer created successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'customer' => $customer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDescription(Request $request)
    {
        $data = json_decode($request->input('description'), true);

        try {
            $order = Order::findOrFail($data['order_id']);
            $can_propagate = $data['can_propagate'] ?? false;

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $propagate = PropagateService::where('order_id', $order->id)
                ->where('service_id', $data['service_id'])
                ->first();

            if ($propagate) {
                $propagate->update([
                    'text' => $data['text']
                ]);
                $message = 'Description updated successfully';
            } else {
                PropagateService::create([
                    'order_id' => $data['order_id'],
                    'service_id' => $data['service_id'],
                    'contract_id' => $order->contract_id,
                    'setting_id' => $order->setting_id,
                    'text' => $data['text']
                ]);
                $message = 'Description created successfully';
            }

            if ($can_propagate) {
                $settings = ContractService::where('contract_id', $order->contract_id)
                    ->where('service_id', $order->setting->service_id)
                    ->get();

                $orders = Order::where('contract_id', $order->contract_id)
                    //->where('setting_id', $order->setting_id)
                    ->whereIn('setting_id', $settings->pluck('id'))
                    ->where('status_id', 1)
                    ->get();

                foreach ($orders as $ord) {
                    PropagateService::updateOrCreate(
                        [
                            'order_id' => $ord->id,
                            'service_id' => $data['service_id'],
                            'contract_id' => $ord->contract_id,
                            'setting_id' => $ord->setting_id,
                        ],
                        [
                            'text' => $data['text'],
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'description' => $propagate
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateNotes(Request $request)
    {
        $data = json_decode($request->input('notes'), true);

        try {
            $order = Order::findOrFail($data['order_id']);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $order->update(['notes' => $data['text']]);

            return response()->json([
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function isPlainText($text)
    {
        if (empty(trim($text))) {
            return false;
        }

        $stripped = strip_tags($text);
        if ($text === $stripped) {
            return true;
        }

        if (nl2br($stripped) === $text) {
            return true;
        }

        // Si contiene etiquetas HTML complejas, no es texto plano
        return false;
    }

    public function formatPlainTextToHtml($text)
    {
        // Normalizar diferentes tipos de saltos de línea
        $text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

        // Eliminar espacios innecesarios
        $text = trim($text);

        // Dividir en párrafos (separados por dos o más saltos de línea)
        $paragraphs = preg_split('/\n\s*\n+/', $text);

        $html = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Convertir saltos de línea simples a <br>
                $paragraph = str_replace("\n", '<br>', $paragraph);
                // Escapar caracteres HTML pero conservar <br>
                $paragraph = htmlspecialchars($paragraph, ENT_NOQUOTES, 'UTF-8', false);
                $paragraph = str_replace('&lt;br&gt;', '<br>', $paragraph);
                // Añadir párrafo con estilos para DOMPDF
                $html .= '<p style="margin: 0px 0 10px 0; line-height: 1.4;">' . $paragraph . '</p>';
            }
        }

        return $html;
    }


    public function searchDevices(Request $request)
    {
        $data = $request->all();

        $floorplans = [];
        if ($data['customer_id'] && $data['service_id']) {
            $floorplans = FloorPlans::where('customer_id', $data['customer_id'])
                ->where('service_id', $data['service_id'])
                ->get();
        }

        $devices = Device::where('floorplan_id', $data['floorplan_id'])->orWhereIn('floorplan_id', $floorplans->pluck('id'))->get();
        return response()->json([
            'devices' => $devices->map(function ($device) {
                return [
                    'id' => $device->id,
                    'name' => $device->controlPoint->name,
                    'code' => $device->code,
                    'nplan' => $device->nplan,
                    'version' => $device->version,
                    'area' => $device->applicationArea->name ?? '-',
                    'floorplan' => [
                        'id' => $device->floorplan_id,
                        'name' => $device->floorplan->filename
                    ],
                    'type' => $device->type_control_point_id,
                    'created_at' => $device->created_at->toDateTimeString()
                ];
            })->toArray()
        ]);
    }

    public function assignDevices(Request $request)
    {
        $devices_data = [];
        $device_ids = json_decode($request->devices, true);
        $order_id = $request->order_id;
        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);

        $order = Order::find($order_id);
        $devices = Device::whereIn('id', $device_ids)->get();

        foreach ($devices as $device) {
            $questions_data = [];
            $questions = $device->controlPoint->questions()->get();

            foreach ($questions as $question) {
                $questions_data[] = [
                    'id' => $question->id,
                    'question' => $question->question,
                    'answer' => $device->incident($order->id, $question->id)->answer ?? null,
                    'answer_default' => $question->answer_default,
                    'answers' => $this->getOptions($question->question_option_id, $answers)
                ];
            }

            $device_states = $device?->states($order->id)->first() ?? null;

            $devices_data[] = [
                'id' => $device->id,
                'nplan' => $device->nplan,
                'code' => $device->code,
                'service' => [
                    'id' => $device->floorplan->service_id,
                    'name' => $device->floorplan->service->name
                ],
                'floorplan' => [
                    'id' => $device->floorplan_id,
                    'name' => $device->floorplan->filename ?? '-',
                ],
                'control_point' => [
                    'id' => $device->controlPoint->id,
                    'name' => $device->controlPoint->name
                ],
                'application_area' => [
                    'id' => $device->application_area_id,
                    'name' => $device->applicationArea->name ?? '-'
                ],
                'questions' => $questions_data,
                'pests' => $device->pests($order->id)->map(function ($dp) {
                    return [
                        'id' => $dp->pest_id,
                        //'pest_id' => $dp->pest_id,
                        'name' => $dp->pest->name,
                        'device_id' => $dp->device_id,
                        'total' => $dp->total,
                    ];
                })->toArray() ?? null,

                'products' => $device->products($order->id)->map(function ($dp) {
                    return [
                        'id' => $dp->product_id,
                        'order_id' => $dp->order_id,
                        'device_id' => $dp->device_id,
                        'application_method_id' => $dp->application_method_id,
                        //'product_id' => $dp->product_id,
                        'lot_id' => $dp->lot_id,
                        'name' => $dp->product->name ?? null,
                        'quantity' => $dp->quantity,
                    ];
                })->toArray() ?? null,
                'states' => [
                    'order_id' => $order->id,
                    'device_id' => $device->id,
                    'is_scanned' => $device_states->is_scanned ?? false,
                    'is_checked' => $device_states->is_checked ?? false,
                    'observations' => $device_states->observations ?? null,
                    'device_image' => $device_states->device_image ?? null
                ]
            ];
        }

        return response()->json([
            'devices' => $devices_data
        ]);
    }

    public function storeEvidence(Request $request, $orderId)
    {
        try {
            // Validar que el order existe
            $order = Order::findOrFail($orderId);

            // Validar los datos de entrada
            /*$validated = $request->validate([
                'evidences' => 'required|array',
                'evidences.*.image' => 'required|string', // base64
                'evidences.*.description' => 'required|string|max:500',
                'evidences.*.area' => 'required|in:servicio,notas,recomendaciones,evidencias',
                'evidences.*.filename' => 'required|string|max:255',
                'evidences.*.filetype' => 'required|string|in:image/jpeg,image/jpg,image/png,image/webp',
                'evidences.*.service_id' => 'nullable|exists:services,id',
                'evidences.*.timestamp' => 'nullable|date',
            ]);*/

            DB::beginTransaction();

            $savedEvidences = [];
            $evidences = $request->input('evidences');

            if ($evidences) {
                foreach ($evidences as $evidenceData) {
                    // Preparar los datos para evidence_data
                    $evidenceDataArray = [
                        'image' => $evidenceData['image'],
                        'description' => $evidenceData['description'],
                        'area' => $evidenceData['area'],
                        'filename' => $evidenceData['filename'],
                        'filetype' => $evidenceData['filetype'],
                        'timestamp' => $evidenceData['timestamp'] ?? now()->toISOString(),
                        'service_id' => $evidenceData['service_id'] ?? null,
                        'original_name' => $evidenceData['filename'],
                        'file_size' => $this->getBase64FileSize($evidenceData['image']),
                    ];

                    // Si la evidencia tiene ID (ya existe), actualizarla
                    if (isset($evidenceData['id']) && !empty($evidenceData['id'])) {
                        $evidence = EvidencePhoto::where('id', $evidenceData['id'])
                            ->where('order_id', $orderId)
                            ->first();

                        if ($evidence) {
                            $evidence->update([
                                'service_id' => $evidenceData['service_id'] ?? null,
                                'evidence_data' => $evidenceDataArray,
                                'filename' => $evidenceData['filename'],
                                'filetype' => $evidenceData['filetype'],
                                'description' => $evidenceData['description'],
                                'area' => $evidenceData['area'],
                            ]);

                            $savedEvidences[] = $evidence->id;
                            continue;
                        }
                    }

                    // Crear nueva evidencia
                    $evidence = EvidencePhoto::create([
                        'order_id' => $orderId,
                        'service_id' => $evidenceData['service_id'] ?? null,
                        'evidence_data' => $evidenceDataArray,
                        'filename' => $evidenceData['filename'],
                        'filetype' => $evidenceData['filetype'],
                        'description' => $evidenceData['description'],
                        'area' => $evidenceData['area'],
                    ]);

                    $savedEvidences[] = $evidence->id;
                }
            }

            EvidencePhoto::whereNotIn('id', $savedEvidences)->where('order_id', $orderId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Evidencias guardadas correctamente',
                'saved_evidences' => $savedEvidences,
                'total_saved' => count($savedEvidences)
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar evidencias: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'evidence_data' => $request->input('evidences')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al guardar las evidencias'
            ], 500);
        }
    }

    /**
     * Obtener el tamaño del archivo base64 en bytes
     */
    private function getBase64FileSize($base64String)
    {
        // Eliminar el prefijo data:image/...;base64,
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);

        // Calcular tamaño aproximado en bytes
        return (int) (strlen($base64) * 3 / 4);
    }

    /**
     * Obtener evidencias existentes de una orden
     */
    public function getEvidences($orderId)
    {
        try {
            // Validar que el order existe
            $order = Order::findOrFail($orderId);

            $evidences = EvidencePhoto::where('order_id', $orderId)
                ->with(['service:id,name']) // Cargar relación con servicio si existe
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($evidence) {
                    return [
                        'id' => $evidence->id,
                        'index' => 'evidence_' . $evidence->id, // Para compatibilidad con frontend
                        'order_id' => $evidence->order_id,
                        'service_id' => $evidence->service_id,
                        'service_name' => $evidence->service ? $evidence->service->name : 'Ninguno',
                        'image' => $evidence->evidence_data['image'] ?? '',
                        'description' => $evidence->description,
                        'area' => $evidence->area,
                        'filename' => $evidence->filename,
                        'filetype' => $evidence->filetype,
                        'timestamp' => $evidence->evidence_data['timestamp'] ?? $evidence->created_at->toISOString(),
                        'created_at' => $evidence->created_at,
                        'updated_at' => $evidence->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'evidences' => $evidences,
                'total' => $evidences->count()
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al obtener evidencias: ' . $e->getMessage(), [
                'order_id' => $orderId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener las evidencias'
            ], 500);
        }
    }

    /**
     * Eliminar una evidencia específica
     */
    public function deleteEvidence($evidenceId)
    {
        try {
            $evidence = EvidencePhoto::findOrFail($evidenceId);
            $evidence->delete();

            return response()->json([
                'success' => true,
                'message' => 'Evidencia eliminada correctamente'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evidencia no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al eliminar evidencia: ' . $e->getMessage(), [
                'evidence_id' => $evidenceId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al eliminar la evidencia'
            ], 500);
        }
    }

    /**
     * Eliminar múltiples evidencias
     */
    public function deleteMultipleEvidences(Request $request)
    {
        try {
            $validated = $request->validate([
                'evidence_ids' => 'required|array',
                'evidence_ids.*' => 'required|exists:evidence_photos,id'
            ]);

            $deletedCount = EvidencePhoto::whereIn('id', $request->evidence_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} evidencias eliminadas correctamente",
                'deleted_count' => $deletedCount
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al eliminar múltiples evidencias: ' . $e->getMessage(), [
                'evidence_ids' => $request->evidence_ids
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al eliminar las evidencias'
            ], 500);
        }
    }
}

