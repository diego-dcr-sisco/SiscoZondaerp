<?php

namespace App\Http\Controllers;

use App\Models\ApplicationArea;
use App\Models\ApplicationMethod;
use App\Models\ControlPointQuestion;
use App\Models\ControlPoint;
use App\Models\Device;
use App\Models\DevicePest;
use App\Models\DeviceProduct;
use App\Models\DeviceStates;
use App\Models\FloorPlans;
use App\Models\MovementProduct;
use App\Models\OpportunityArea;
use App\Models\Order;
use App\Models\OrderIncidents;
use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\OrderTechnician;
use App\Models\OrderPest;
use App\Models\PestCatalog;
use App\Models\ProductCatalog;
use App\Models\Question;
use App\Models\Lot;
use App\Models\Technician;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseMovement;
use App\Models\WarehouseMovementOrder;
use App\Models\WarehouseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;


class AppController extends Controller
{
	private $file_answers_path = 'datas/json/answers.json';

	private function getOptions($id, $answers)
	{
		foreach ($answers as $answer) {
			if ($answer['id'] == $id) {
				return $answer['options'];
			}
		}
		return [];
	}

	function getCsrfToken()
	{
		return response()->json(Session::token());
	}

	public function login(Request $request)
	{
		$request->validate([
			'email' => 'required|email',
			'password' => 'required',
		]);

		$user = User::where('email', $request->email)->first();

		if (!$user || !Hash::check($request->password, $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['Las credenciales proporcionadas son incorrectas.'],
			]);
		}

		// Si estás usando tokens de Sanctum
		$token = $user->createToken('auth-token')->plainTextToken;

		return response()->json([
			'userId' => $user->id,
			'email' => $user->email,
			'username' => $user->name,
			'token' => $token,
		], 200);
	}

	public function getUsers()
	{
		$data = User::where('role_id', 3)->get();
		return response()->json($data);
	}

	public function orders(int $id, string $date)
	{
		$data = [];
		$reports = [];
		try {
			$orders = [];
			$user = User::find($id);
			$answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);


			if ($user) {
				//$date_range = urldecode($dates);

				// Verifica si el usuario es técnico (rol = 3 para técnicos)
				if ($user->role_id == 3 || $user->work_department_id == 8) {
					$tech = Technician::where('user_id', $user->id)->first();
					$orderIds = OrderTechnician::where('technician_id', $tech->id)->pluck('order_id');
					$orders = Order::whereIn('id', $orderIds)
						->where('programmed_date', $date)
						->whereNotIn('status_id', [5, 6])
						->get();
				} else {
					$orders = Order::where('programmed_date', $date)
						->whereNotIn('status_id', [5, 6])
						->get();
				}
			}

			// Ajustes para un nuevo JSON
			foreach ($orders as $order) {
				$services_data = [];
				$services = $order->services()->get();

				foreach ($services as $service) {
					$devices_ids = [];
					$devices_data = [];

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

					if ($service->prefix == 1) {
						$floorplans = FloorPlans::where('service_id', $service->id)->where('customer_id', $order->customer->id)->get();
						foreach ($floorplans as $floorplan) {
							$version = $floorplan->versionByDate($order->programmed_date);
							if ($version) {
								$devices = Device::where('floorplan_id', $floorplan->id)
									->where('version', $version)
									->pluck('id') // Solo obtenemos los IDs
									->toArray();

								$devices_ids = array_unique(array_merge($devices_ids, $devices));
							}
						}
					}

					$devices = Device::whereIn('id', $devices_ids)->orderBy('nplan')->get();

					foreach ($devices as $device) {
						$questions_data = [];
						foreach ($device->questions as $quest) {
							$questions_data[] = [
								'id' => $quest->id,
								'question' => $quest->question,
								'options' => $this->getOptions($quest->question_option_id, $answers)
							];
						}

						$devices_data[] = [
							'id' => $device->id,
							'nplan' => $device->nplan,
							'code' => $device->code,
							'area' => [
								'id' => $device->application_area_id,
								'name' => $device->applicationArea->name
							],
							'control_point' => [
								'id' => $device->type_control_point_id,
								'name' => $device->controlPoint->name,
								'code' => $device->controlPoint->code
							],
							'floorplan' => [
								'id' => $device->floorplan->id,
								'name' => $device->floorplan->filename,
								'service_name' => $device->floorplan->service->name
							],
							'questions' => $questions_data
						];
					}

					$services_data[] = [
						'id' => $service->id,
						'prefix' => $service->id == 51 && empty($serviceWithDevices) ? 4 : $service->prefix,
						'name' => $service->name,
						'description' => $service->description ?? $order->propagateByService($service->id)->text ?? null,
						'pests' => $pests->toArray(),
						'products' => $products->toArray(),
						'application_methods' => $application_methods->toArray(),
						'devices' => $devices_data,
					];
				}

				$data[] = [
					'id' => $order->id,
					'folio' => $order->folio,
					'status_id' => $order->status_id,
					'start_time' => $order->start_time,
					'programmed_date' => $order->programmed_date,
					'execution' => $order->execution,
					'areas' => $order->areas,
					'additional_comments' => $order->additional_comments,
					'price' => $order->price ?? 0,
					'signature' => $order->signature,
					"updated_at" => $order->updated_at,
					'service_type' => $order->customer->serviceType->name,
					'customer' => [
						'name' => $order->customer->name ?? '-',
						'address' => $order->customer->address ?? '-',
						'phone' => $order->customer->phone ?? '-',
						'map_url' => $order->customer->map_location_url
					],
					'services' => $services_data,
					'address' => $order->customer->address,
				];

				$reports[] = $this->constructReport($order->id);
			}

		} catch (\Exception $e) {
			return response()->json(['error' => 'Error procesando fechas: ' . $e->getMessage()], 500);
		}

		return response()->json([
			'orders' => $data,
			'reports' => $reports,
		], 200);
	}

	function handleReport(Request $request): JsonResponse
	{
		$updated_order_pests = [];
		$updated_order_products = [];

		if (!$request->all()) {
			return response()->json(['error' => 'No se recibieron datos'], 400);
		}

		$data = $request->all();

		try {
			$order = Order::find($data['order_id']);
			if ($order && $order->status_id == 1) {
				$order->start_time = Carbon::parse($data['start_time'])->format('H:i:s') ?? null;
				$order->end_time = Carbon::parse($data['end_time'])->format('H:i:s') ?? null;
				$order->completed_date = Carbon::parse($data['completed_date'])->format('Y-m-d') ?? null;
				$order->notes = $data['notes'] ?? null;
				$order->customer_signature = $data['customer_signature'] ?? null;
				$order->closed_by = $data['user_id'] ?? null;
				$order->synchronized_by = $data['user_id'] ?? null;
				$order->synchronized_at = now();
				$order->status_id = 3;
				$order->save();

				$user = User::find($data['user_id']);
				$products_data = $data['products'];
				$pests_data = $data['pests'];
				$reviews = $data['reviews'];

				$technician = Technician::where('user_id', $user->id)->first();

				$products_amount = [];
				$aux_data = [];

				foreach ($products_data as $product_data) {
					$productId = $product_data['product_id'];
					$products_amount[$productId] = ($products_amount[$productId] ?? 0) + $product_data['amount'];
				}

				foreach ($products_data as $product_data) {
					$productId = $product_data['product_id'];
					$aux_data[] = [
						'product_id' => $productId,
						'service_id' => $product_data['service_id'],
						'lot_id' => $product_data['lot_id'] ?? null,
						'app_method_id' => $product_data['app_method_id'] ?? null,
						'amount' => $products_amount[$productId],
					];
				}

				$products_data = $aux_data;

				foreach ($pests_data as $pest_data) {
					$ord_p = OrderPest::updateOrCreate([
						'order_id' => $order->id,
						'service_id' => $pest_data['service_id'],
						'pest_id' => $pest_data['pest_id'],
					], [
						'total' => $pest_data['count'],
					]);

					$updated_order_pests[] = $ord_p->id;
				}

				OrderPest::whereNotIn('id', $updated_order_pests)->delete();

				$reviews_has_products = false;
				$reviews_has_pests = false;

				foreach ($reviews as $review) {
					$updated_products = [];
					$updated_pests = [];

					foreach ($review['answers'] as $answer) {
						OrderIncidents::updateOrCreate(
							[
								'order_id' => $order->id,
								'question_id' => $answer['question_id'],
								'device_id' => $review['device_id'],
							],
							[
								'answer' => $answer['response'],
							]
						);

						DeviceStates::updateOrCreate(
							[
								'order_id' => $order->id,
								'device_id' => $review['device_id']
							],
							[
								'is_scanned' => $review['is_scanned'],
								'is_checked' => $review['is_checked'],
								'observations' => $review['observations'] ?? null,
								'device_image' => $review['image'] ?? null
							]
						);
					}

					foreach ($review['pests'] as $pest) {
						DevicePest::updateOrCreate([
							'order_id' => $order->id,
							'device_id' => $review['device_id'],
							'pest_id' => $pest['pest_id'],

						], ['total' => $pest['count']]);
						$reviews_has_pests = true;
					}

					foreach ($review['products'] as $product) {
						DeviceProduct::updateOrCreate(
							[
								'order_id' => $order->id,
								'device_id' => $review['device_id'],
								'product_id' => $product['product_id'],
							],
							[
								'application_method_id' => $product['app_method_id'],
								'lot_id' => $product['lot_id'],
								'quantity' => $product['amount'],
								'possible_lot' => null,
							]
						);
						$reviews_has_products = true;
					}

					DevicePest::where('order_id', $order->id)->where('device_id', $review['device_id'])
						->whereNotIn('pest_id', $updated_pests)
						->delete();

					DeviceProduct::where('order_id', $order->id)->where('device_id', $review['device_id'])
						->whereNotIn('product_id', $updated_products)
						->delete();
				}

				if ($reviews_has_products && $reviews_has_pests) {
					$products_data = [];

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
				}

				$this->handleStock($order, $products_data, $technician, $user);

				return response()->json(['message' => 'Reporte recibido'], 200);
			} else {
				// Si la orden no existe o ya fue procesada, devolvemos un error.
				$message = $order ? 'El reporte de la orden #'. $order->id . '(' . $order->folio . ')' . ' está cerrado. No se puede sincronizar.' : 'Orden #' . $data['order_id'] . ' NO fue encontrada. Favor de contactar a soporte';
				return response()->json([
					'error' => 'No se puede sincronizar el reporte.',
					'message' => $message
				], 409);
			}
		} catch (\Exception $e) {
			Log::critical('Error al procesar reporte completo', [
				'error' => $e->getMessage(),
				'data' => $data,
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'error' => 'Ocurrió un error al procesar el reporte',
				'message' => config('app.debug') ? $e->getMessage() : null
			], 500);
		}
	}

	public function handleStock($order, $products_data, $technician, $user)
	{
		$updated_products = [];
		$updated_lots = [];
		$updated_order_products = [];

		$warehouse = null;
		if ($technician) {
			$warehouse = Warehouse::where('technician_id', $technician->id)->first();
		}

		foreach ($products_data as $product_data) {
			$wm = null;
			$product = ProductCatalog::find($product_data['product_id']);
			$op = OrderProduct::updateOrCreate([
				'order_id' => $order->id,
				'product_id' => $product->id,
				'lot_id' => $product_data['lot_id'],
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
					],
					[
						'user_id' => $user->id,
						'observations' => 'Movimiento realizado en la order #' . $order->folio . ' | ID: ' . $order->id,
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
					'lot_id' => $product_data['lot_id'],
					'amount' => $product_data['amount'],
				]);

				$updated_products[] = $mp->product_id;
				$updated_lots[] = $mp->lot_id;
			}

			WarehouseOrder::updateOrCreate([
				'movement_id' => 8,
				'order_id' => $order->id,
				'user_id' => $user->id,
			], [
				'warehouse_id' => $warehouse->id ?? null,
				'warehouse_movement_id' => $wm->id ?? null,
				'product_id' => $product_data['product_id'],
				'lot_id' => $product_data['lot_id'] ?? null,
				'amount' => $product_data['amount'],
			]);
		}

		if (count($updated_products) > 0 || count($updated_lots) > 0) {
			MovementProduct::where('warehouse_id', $warehouse->id)->where('movement_id', 8)
				->whereNotIn('product_id', $updated_products)
				->whereNotIn('lot_id', $updated_lots)
				->delete();
		}

		OrderProduct::where('order_id', $order->id)->whereNotIn('id', $updated_order_products)->delete();
	}

	private function constructReport($order_id)
	{
		$order = Order::find($order_id);
		$service = $order->services()->first();

		$order_reviews = [];
		$order_products = [];
		$order_pests = [];

		$incidents = $order->incidents()->get();
		$devices = Device::whereIn('id', $incidents->pluck('device_id')->unique())->get();

		foreach ($devices as $device) {
			$pests = $device->pests($order_id);
			$products = $device->products($order_id);
			$reviews = OrderIncidents::where('order_id', $order_id)->where('device_id', $device->id)->get();
			$states = $device->states($order_id)->first();

			$order_reviews[] = [
				'device_id' => $device->id,
				'pests' => $pests->map(function ($p) use ($service) {
					return [
						'pest_id' => $p->pest_id,
						'service_id' => $service->id,
						'count' => $p->total
					];
				}),
				'products' => $products->map(function ($p) use ($service) {
					return [
						'name' => $p->product->name,
						'product_id' => $p->product_id,
						'service_id' => $service->id,
						'lot_id' => $p->lot_id,
						'app_method_id' => $p->application_method_id,
						'amount' => $p->quantity,
						'metric' => $p->product->metric->value,
					];
				}),
				'answers' => $reviews->map(function ($r) {
					return [
						'question_id' => $r->question_id,
						'response' => $r->answer,
					];
				}),
				'image' => $states->device_image,
				'observations' => $states->observations,
				'is_scanned' => $states->is_scanned,
				'is_checked' => true,
			];
		}

		if (count($order_reviews) > 0) {
			$products = OrderProduct::where('order_id', $order_id)->get();
			$order_products = $products->map(function ($p) {
				return [
					'name' => $p->product->name,
					'product_id' => $p->product_id,
					'service_id' => $p->service_id,
					'lot_id' => $p->lot_id,
					'app_method_id' => $p->application_method_id,
					'amount' => $p->amount,
					'metric' => $p->product->metric->value,
				];
			});
		}

		$pests = OrderPest::where('order_id', $order_id)->get();
		$order_pests = $pests->map(function ($p) {
			return [
				'pest_id' => $p->pest_id,
				'service_id' => $p->service_id,
				'count' => $p->total,
			];
		});

		$data = [
			'order_id' => $order->id,
			'user_id' => $order->closed_by,
			'status_id' => $order->status_id,
			'start_time' => $order->start_time,
			'end_time' => $order->end_time,
			'completed_date' => $order->completed_date,
			'notes' => $order->notes,
			'customer_signature' => $order->customer_signature,
			'signature_name' => $order->signature_name,

			'reviews' => $order_reviews,
			'products' => $order_products,
			'pests' => $order_pests,

			'finalized_at' => null,
			'reopened_at' => null,
			'is_finalized' => $order->status_id == 3 ? true : false,
			'is_synchronized' => $order->synchronized_by != null ? true : false,
		];

		return $data;
	}

}