<?php

namespace App\Http\Controllers;

use App\Models\ComercialZoneCustomer;
use App\Models\Consumption;
use App\Models\Customer;
use App\Models\CustomerZone;
use App\Models\WarehouseOrder;
use App\Models\Zone;
use App\Models\ProductCatalog;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ConsumptionSupply;
use App\Models\RotationPlan;
use App\Models\ComercialZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ConsumptionController extends Controller
{
    private $size = 50; // Paginación

    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Consumos en ordenes' => '/stock/movements/orders',
        'Consumos' => '/consumptions/',
        // 'Zonas' => '/customer-zones',
        // 'Pedidos' => '/consumptions',
        // 'Productos en ordenes' => '/stock/orders-products',
        //'Estadisticas' => '/stock/analytics',
        // 'Compras' => '/purchase-requisition/purchases',
    ];

    public function preIndex()
    {
        return view('stock.consumptions.pre-index');
    }

    public function index(Request $request)
    {
        $consumptions_data = [];
        $last_start = Carbon::now()->startOfMonth()->subMonth();
        $last_end = Carbon::now()->subMonth()->endOfMonth();

        $orders_query = Order::query();

        // Almacenar los valores de los filtros para pasarlos a la vista
        $filters = [
            'comercial_zone' => $request->comercial_zone,
            'customers' => $request->customers,
            'date' => $request->date
        ];

        if ($request->filled('comercial_zone')) {
            $comercial_zone = ComercialZone::find($request->comercial_zone);
            $customers = $comercial_zone->customers();
            $orders_query = $orders_query->whereIn('customer_id', $customers->pluck('customer.id'));
        }

        if ($request->filled('customers')) {
            $customer_names = explode(",", $request->customers);
            $customers_query = Customer::query();
            foreach ($customer_names as $customer_name) {
                $customers_query->orWhere('name', 'LIKE', "%{$customer_name}%");
            }
            $customers = $customers_query->get();
            $orders_query = $orders_query->whereIn('customer_id', $customers->pluck('id'));
        }

        if ($request->filled('date')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->date));

            // CORRECCIÓN: Usar las fechas del filtro en lugar de las fechas por defecto
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $orders_query = $orders_query->whereBetween('programmed_date', [$startDate, $endDate]);
        }

        $orders = $orders_query->get();
        $wos = WarehouseOrder::whereIn('order_id', $orders_query->pluck('id'))->get();

        foreach ($wos as $wo) {
            $product_id = $wo->product_id;
            $customer_id = $wo->order->customer_id;

            $key = $product_id . '_' . $customer_id;

            if (isset($consumptions_data[$key])) {
                $consumptions_data[$key]['amount'] += $wo->amount;
            } else {
                $czcs = ComercialZoneCustomer::where('customer_id', $wo->order->customer_id)->get();
                $comercial_zones = ComercialZone::whereIn('id', $czcs->pluck('comercial_zone_id'))->orderBy('name')->get();

                $consumptions_data[$key] = [
                    'key' => $key,
                    'product_id' => $product_id,
                    'product_name' => $wo->product->name,
                    'product_metric' => $wo->product->metric->value,
                    'customer_id' => $customer_id,
                    'customer_name' => $wo->order->customer->name,
                    'amount' => $wo->amount,
                    'comercial_zones' => $comercial_zones->pluck('name', 'code')->toArray(),
                    'timelapse' => $request->filled('date') ? $request->date : $last_start->format('d/m/Y') . ' - ' . $last_end->format('d/m/Y')
                ];
            }
        }

        $consumptions_data = array_values($consumptions_data);
        $comercial_zones = ComercialZone::orderBy('name')->get();
        $navigation = $this->navigation;

        //dd($filters);   

        // Pasar los filtros a la vista
        return view('stock.consumptions.table', compact(
            'comercial_zones',
            'consumptions_data',
            'navigation',
            'filters' // ← Añadir los filtros aquí
        ));
    }

    public function create()
    {
        $navigation = $this->navigation;
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $products = ProductCatalog::select('id', 'name')->orderBy('name')->get();
        $zones = Zone::select('id', 'name')->orderBy('name')->get();

        return view('stock.consumptions.create.index', compact(
            'customers',
            'products',
            'zones',
            'navigation'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer,id',
            'zone' => 'required|exists:zones,id',
            'request_month' => 'required|integer|between:1,12',
            'products' => 'required|string',
            'observations' => 'nullable|string|max:1000',
        ], [
            'customer_id.required' => 'Debe seleccionar un cliente',
            'zone.required' => 'Debe seleccionar una zona',
            'request_month.required' => 'El mes es obligatorio',
            'request_month.integer' => 'El mes debe ser un número válido',
            'request_month.between' => 'El mes debe estar entre 1 y 12',
            'products.required' => 'Debe agregar al menos un producto',
        ]);

        // Decodificar la lista de productos JSON
        $products = json_decode($request->products, true);

        if (!$products || !is_array($products) || count($products) === 0) {
            return back()->withInput()->with('error', 'Debe agregar al menos un producto válido');
        }

        $currentYear = now()->year;
        $createdConsumptions = [];
        $errors = [];

        // Procesar cada producto de la lista
        foreach ($products as $product) {
            try {
                // Verificar duplicados para este producto específico
                $exists = Consumption::where('customer_id', $request->customer_id)
                    ->where('zone_id', $request->zone)
                    ->where('product_id', $product['product_id'])
                    ->where('month', $request->request_month)
                    ->where('year', $currentYear)
                    ->exists();

                if ($exists) {
                    $productName = ProductCatalog::find($product['product_id'])->name ?? 'Producto desconocido';
                    $errors[] = "Ya existe un registro de consumo para el producto: {$productName} en este período";
                    continue;
                }

                // Crear el registro de consumo
                $consumptionData = [
                    'customer_id' => $request->customer_id,
                    'zone_id' => $request->zone,
                    'product_id' => $product['product_id'],
                    'amount' => $product['quantity'],
                    'units' => $product['unit'],
                    'month' => (int) $request->request_month,
                    'year' => $currentYear,
                    'status' => 'pending',
                    'observation' => $request->observations,
                    'user_id' => auth()->id(),
                ];

                $consumption = Consumption::create($consumptionData);
                $createdConsumptions[] = $consumption;

            } catch (\Exception $e) {
                $productName = ProductCatalog::find($product['product_id'])->name ?? 'Producto desconocido';
                $errors[] = "Error al guardar el producto {$productName}: " . $e->getMessage();
            }
        }

        // Verificar resultados
        if (count($createdConsumptions) === 0) {
            $errorMessage = count($errors) > 0 ? implode(', ', $errors) : 'No se pudo guardar ningún consumo';
            return back()->withInput()->with('error', $errorMessage);
        }

        $message = count($createdConsumptions) . ' consumo(s) registrado(s) exitosamente';
        if (count($errors) > 0) {
            $message .= '. Errores: ' . implode(', ', $errors);
        }

        return redirect()->route('consumptions.index')->with('success', $message);
    }

    public function show(string $id)
    {
        $navigation = $this->navigation;
        $consumption = Consumption::with(['customer', 'zone', 'product', 'user'])->findOrFail($id);
        return view('stock.consumptions.show', compact('consumption', 'navigation'));
    }

    /**
     * Mostrar los detalles de un consumo agrupado (todos los productos del mismo período)
     */
    public function showGrouped(Request $request)
    {
        $navigation = $this->navigation;
        $request->validate([
            'customer_id' => 'required|exists:customer,id',
            'zone_id' => 'required|exists:zones,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        // Obtener todos los registros del consumo agrupado
        $consumptions = Consumption::with(['customer', 'zone', 'product', 'user'])
            ->where('customer_id', $request->customer_id)
            ->where('zone_id', $request->zone_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($consumptions->isEmpty()) {
            abort(404, 'Consumo no encontrado');
        }

        $firstConsumption = $consumptions->first();

        // Crear objeto con información del consumo agrupado
        $groupedConsumption = (object) [
            'customer' => $firstConsumption->customer,
            'zone' => $firstConsumption->zone,
            'month' => $firstConsumption->month,
            'year' => $firstConsumption->year,
            'month_spanish' => $firstConsumption->month_spanish,
            'period_formatted' => $firstConsumption->period_formatted,
            'status' => $firstConsumption->status,
            'status_formatted' => $firstConsumption->status_formatted,
            'observation' => $firstConsumption->observation,
            'user' => $firstConsumption->user,
            'created_at' => $firstConsumption->created_at,
            'updated_at' => $consumptions->max('updated_at'),
            'products_count' => $consumptions->count(),
            'total_amount' => $consumptions->sum('amount'),
            'products' => $consumptions,
        ];

        // dd($groupedConsumption);

        return view('stock.consumptions.new.show-grouped', compact('groupedConsumption', 'navigation'));
    }

    public function edit(string $id)
    {
        $navigation = $this->navigation;
        // Obtener el consumo individual para extraer información del grupo
        $consumption = Consumption::with(['customer', 'zone'])->findOrFail($id);

        // Obtener todos los consumos del mismo grupo (mismo cliente, zona, mes y año)
        $groupedConsumptions = Consumption::with(['customer', 'zone', 'product', 'user'])
            ->where('customer_id', $consumption->customer_id)
            ->where('zone_id', $consumption->zone_id)
            ->where('month', $consumption->month)
            ->where('year', $consumption->year)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($groupedConsumptions->isEmpty()) {
            abort(404, 'Consumo no encontrado');
        }

        $firstConsumption = $groupedConsumptions->first();

        // Crear objeto con información del consumo agrupado
        $groupedConsumption = (object) [
            'id' => $firstConsumption->id,
            'ids' => $groupedConsumptions->pluck('id'),
            'customer' => $firstConsumption->customer,
            'zone' => $firstConsumption->zone,
            'month' => $firstConsumption->month,
            'year' => $firstConsumption->year,
            'month_spanish' => $firstConsumption->month_spanish,
            'period_formatted' => $firstConsumption->period_formatted,
            'status' => $firstConsumption->status,
            'status_formatted' => $firstConsumption->status_formatted,
            'observation' => $firstConsumption->observation,
            'user' => $firstConsumption->user,
            'created_at' => $firstConsumption->created_at,
            'updated_at' => $groupedConsumptions->max('updated_at'),
            'products_count' => $groupedConsumptions->count(),
            'total_amount' => $groupedConsumptions->sum('amount'),
            'products' => $groupedConsumptions,
        ];

        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $zones = CustomerZone::where('customer_id', $consumption->customer_id)
            ->select('id', 'zone_id')->with('zone')->orderBy('zone_id')->get();
        $products = ProductCatalog::select('id', 'name')->orderBy('name')->get();
        $months = Consumption::MONTH_NAMES;
        $years = range(date('Y') - 5, date('Y') + 1);

        return view('stock.consumptions.new.edit', compact(
            'consumption',
            'groupedConsumption',
            'customers',
            'zones',
            'products',
            'months',
            'years',
            'navigation'
        ));
    }

    public function update(Request $request, string $id)
    {
        $navigation = $this->navigation;
        $consumption = Consumption::findOrFail($id);

        $request->validate([
            'customer' => 'required|exists:customer,id',
            'zone' => 'nullable|exists:customer_zones,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 5),
            'status' => 'nullable|in:pending,approved,rejected',
            'observation' => 'nullable|string|max:500',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:product_catalog,id',
            'products.*.amount' => 'required|numeric|min:0',
            'products.*.units' => 'nullable|string|max:50'
        ]);

        // Obtener todos los productos existentes del grupo
        $existingProducts = Consumption::where('customer_id', $consumption->customer_id)
            ->where('zone_id', $consumption->zone_id)
            ->where('month', $consumption->month)
            ->where('year', $consumption->year)
            ->get();

        // Recopilar IDs de productos que se van a mantener
        $productIdsToKeep = collect($request->products)
            ->filter(function ($product) {
                return !empty($product['id']);
            })
            ->pluck('id')
            ->toArray();

        // Eliminar productos que ya no están en la lista
        foreach ($existingProducts as $existingProduct) {
            if (!in_array($existingProduct->id, $productIdsToKeep)) {
                $existingProduct->delete();
            }
        }

        // Actualizar datos principales del primer consumo (para el grupo)
        $consumption->update([
            'customer_id' => $request->customer,
            'zone_id' => $request->zone,
            'month' => $request->month,
            'year' => $request->year,
            'status' => $request->status,
            'observation' => $request->observation
        ]);

        // Actualizar también los demás productos del grupo con los mismos datos generales
        Consumption::where('customer_id', $consumption->customer_id)
            ->where('zone_id', $consumption->zone_id)
            ->where('month', $consumption->month)
            ->where('year', $consumption->year)
            ->where('id', '!=', $consumption->id)
            ->update([
                'customer_id' => $request->customer,
                'zone_id' => $request->zone,
                'month' => $request->month,
                'year' => $request->year,
                'status' => $request->status,
                'observation' => $request->observation
            ]);

        // Procesar cada producto de la solicitud
        foreach ($request->products as $productData) {
            if (!empty($productData['id'])) {
                // Actualizar producto existente
                $product = Consumption::find($productData['id']);
                if ($product) {
                    $product->update([
                        'customer_id' => $request->customer,
                        'zone_id' => $request->zone,
                        'product_id' => $productData['product_id'],
                        'amount' => $productData['amount'],
                        'units' => $productData['units'] ?? null,
                        'month' => $request->month,
                        'year' => $request->year,
                        'status' => $request->status,
                        'observation' => $request->observation
                    ]);
                }
            } else {
                // Crear nuevo producto
                // Verificar que no exista ya un producto igual en este período
                $exists = Consumption::where('customer_id', $request->customer)
                    ->where('zone_id', $request->zone)
                    ->where('product_id', $productData['product_id'])
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->exists();

                if (!$exists) {
                    Consumption::create([
                        'customer_id' => $request->customer,
                        'zone_id' => $request->zone,
                        'product_id' => $productData['product_id'],
                        'amount' => $productData['amount'],
                        'units' => $productData['units'] ?? null,
                        'month' => $request->month,
                        'year' => $request->year,
                        'status' => $request->status ?? 'pending',
                        'observation' => $request->observation,
                        'user_id' => auth()->id()
                    ]);
                }
            }
        }

        return redirect()->route('consumptions.index')
            ->with('success', 'Consumo actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $navigation = $this->navigation;
        $consumption = Consumption::findOrFail($id);

        // Eliminar archivo si existe
        if ($consumption->file && Storage::disk('public')->exists($consumption->file)) {
            Storage::disk('public')->delete($consumption->file);
        }

        $consumption->delete();

        return redirect()->route('consumptions.index')
            ->with('success', 'Consumo eliminado exitosamente');
    }

    /**
     * Eliminar un grupo completo de consumos (todos los productos del mismo período)
     */
    public function destroyGroup(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer,id',
            'zone_id' => 'required|exists:zones,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        // Obtener todos los registros del grupo
        $consumptions = Consumption::where('customer_id', $request->customer_id)
            ->where('zone_id', $request->zone_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->get();

        if ($consumptions->isEmpty()) {
            return redirect()->route('consumptions.index')
                ->with('error', 'No se encontraron consumos para eliminar');
        }

        $deletedCount = 0;
        $customer = $consumptions->first()->customer;
        $monthName = Consumption::MONTH_NAMES[$request->month] ?? $request->month;

        // Eliminar archivos y registros
        foreach ($consumptions as $consumption) {
            // Eliminar archivo si existe
            if ($consumption->file && Storage::disk('public')->exists($consumption->file)) {
                Storage::disk('public')->delete($consumption->file);
            }

            $consumption->delete();
            $deletedCount++;
        }

        return redirect()->route('consumptions.index')
            ->with('success', "Se eliminaron {$deletedCount} productos del consumo de {$customer->name} para {$monthName} {$request->year}");
    }

    /**
     * Cambiar estado del consumo
     */
    public function changeStatus(Request $request, string $id)
    {
        $consumption = Consumption::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $consumption->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'status' => $consumption->status_formatted
        ]);
    }

    public function exportTotalConsumption(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $customerId = $request->get('customer_id');

        // Obtener los datos de consumo usando el método existente
        $query = OrderProduct::with(['product.metric'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($customerId) {
            $query->whereHas('order', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        $consumptions = $query->get()
            ->groupBy('product_id')
            ->map(function ($group) {
                return [
                    'product' => $group->first()->product,
                    'amount' => $group->sum('amount')
                ];
            })->values();

        // Crear nuevo spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer encabezados
        $sheet->setCellValue('A1', 'Reporte de Consumos Totales');
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A2', 'Período: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A2:D2');

        if ($customerId) {
            $customer = Customer::find($customerId);
            $sheet->setCellValue('A3', 'Cliente: ' . ($customer ? $customer->name : 'N/A'));
            $sheet->mergeCells('A3:D3');
            $row = 5;
        } else {
            $row = 4;
        }

        // Encabezados de tabla
        $headers = ['#', 'Producto', 'Cantidad', 'Unidad'];
        $sheet->fromArray($headers, null, 'A' . $row);
        $row++;

        // Datos
        foreach ($consumptions as $index => $consumption) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $consumption['product']->name);
            $sheet->setCellValue('C' . $row, $consumption['amount']);
            $sheet->setCellValue('D' . $row, $consumption['product']->metric->value);
            $row++;
        }

        // Estilo
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A4:D' . ($row - 1))->applyFromArray($styleArray);
        $sheet->getStyle('A1:D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-size columnas
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'Consumos_Totales_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function exportProductConsumption(Request $request, $product_id)
    {
        $product = ProductCatalog::findOrFail($product_id);
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Obtener los datos de consumo
        $orders = OrderProduct::where('product_id', $product_id)
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('programmed_date', [$startDate, $endDate]);
            })
            ->with(['order.customer', 'service'])
            ->get();

        // Crear nuevo spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer encabezados
        $sheet->setCellValue('A1', 'Reporte de Consumo');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'Producto: ' . $product->name);
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A3', 'Período: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A3:F3');

        // Encabezados de tabla
        $headers = ['#', 'ID Orden', 'Fecha Programada', 'Cliente', 'Servicio', 'Cantidad'];
        $sheet->fromArray($headers, null, 'A5');

        // Datos
        $row = 6;
        $total = 0;
        foreach ($orders as $index => $order) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $order->order_id);
            $sheet->setCellValue('C' . $row, $order->order->programmed_date);
            $sheet->setCellValue('D' . $row, $order->order->customer->name);
            $sheet->setCellValue('E' . $row, $order->service->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $order->amount . ' ' . $product->metric->value);

            $total += $order->amount;
            $row++;
        }

        // Total
        $sheet->setCellValue('A' . $row, 'Total:');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('F' . $row, $total . ' ' . $product->metric->value);

        // Estilo
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A5:F' . $row)->applyFromArray($styleArray);
        $sheet->getStyle('A1:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-size columnas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear archivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'Consumo_' . $product->name . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    /**
     * Obtener clientes por zona para filtrado dinámico
     */
    public function getCustomersByZone(Request $request)
    {
        $zoneId = $request->get('zone_id');

        if (!$zoneId) {
            // Si no hay zona seleccionada, devolver todos los clientes
            $customers = Customer::select('id', 'name')->orderBy('name')->get();
        } else {
            // Obtener solo los clientes relacionados con la zona seleccionada
            try {
                $customers = Customer::select('customer.id', 'customer.name')
                    ->join('customer_zones', 'customer.id', '=', 'customer_zones.customer_id')
                    ->where('customer_zones.zone_id', $zoneId);

                // Intentar filtrar por status si la columna existe
                try {
                    $customers = $customers->where('customer_zones.status', 'active');
                } catch (\Exception $e) {
                    // Si la columna status no existe, continuar sin filtrar por status
                }

                $customers = $customers->orderBy('customer.name')
                    ->distinct()
                    ->get();

            } catch (\Exception $e) {
                // Si hay error en la consulta, devolver todos los clientes
                $customers = Customer::select('id', 'name')->orderBy('name')->get();
            }
        }

        return response()->json($customers);
    }

    /**
     * Mostrar la vista de surtir productos para un consumo agrupado
     */
    public function showSupplyGrouped(Request $request)
    {
        $navigation = $this->navigation;
        $request->validate([
            'customer_id' => 'required|exists:customer,id',
            'zone_id' => 'required|exists:zones,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        // Obtener todos los registros del consumo agrupado con información de surtido
        $consumptions = Consumption::with(['customer', 'zone', 'product', 'user', 'supply'])
            ->where('customer_id', $request->customer_id)
            ->where('zone_id', $request->zone_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($consumptions->isEmpty()) {
            abort(404, 'Consumo no encontrado');
        }

        $firstConsumption = $consumptions->first();

        // Crear objeto con información del consumo agrupado
        $groupedConsumption = (object) [
            'customer' => $firstConsumption->customer,
            'zone' => $firstConsumption->zone,
            'month' => $firstConsumption->month,
            'year' => $firstConsumption->year,
            'month_spanish' => $firstConsumption->month_spanish,
            'period_formatted' => $firstConsumption->period_formatted,
            'status' => $firstConsumption->status,
            'status_formatted' => $firstConsumption->status_formatted,
            'observation' => $firstConsumption->observation,
            'user' => $firstConsumption->user,
            'created_at' => $firstConsumption->created_at,
            'updated_at' => $consumptions->max('updated_at'),
            'products_count' => $consumptions->count(),
            'total_amount' => $consumptions->sum('amount'),
            'products' => $consumptions,
        ];

        return view('stock.consumptions.new.supply-grouped', compact('groupedConsumption', 'navigation'));
    }

    /**
     * Actualizar información de surtido para productos
     */
    public function updateSupply(Request $request)
    {
        $request->validate([
            'supplies' => 'required|array',
            'supplies.*.consumption_id' => 'required|exists:consumptions,id',
            'supplies.*.is_supplied' => 'boolean',
            'supplies.*.supplied_amount' => 'nullable|numeric|min:0',
            'supplies.*.supply_notes' => 'nullable|string|max:500',
        ]);

        $updated = 0;
        $errors = [];

        foreach ($request->supplies as $supplyData) {
            try {
                $consumption = Consumption::findOrFail($supplyData['consumption_id']);

                // Crear o actualizar el registro de surtido
                $supply = ConsumptionSupply::updateOrCreate(
                    ['consumption_id' => $consumption->id],
                    [
                        'is_supplied' => $supplyData['is_supplied'] ?? false,
                        'supplied_amount' => $supplyData['supplied_amount'] ?? 0,
                        'supply_notes' => $supplyData['supply_notes'] ?? null,
                        'supplied_by' => auth()->id(),
                        'supplied_at' => $supplyData['is_supplied'] ? now() : null,
                    ]
                );

                $updated++;
            } catch (\Exception $e) {
                $errors[] = "Error al actualizar el producto ID {$supplyData['consumption_id']}: " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('warning', "Se actualizaron {$updated} productos. Errores: " . implode(', ', $errors));
        }

        return redirect()->back()
            ->with('success', "Se actualizó la información de surtido para {$updated} producto(s) exitosamente");
    }


    ///////////////////////  CONSUMOS PASADOS  ///////////////////////

    //Función para obtener todas las ordenes existentes
    public function getPastConsumptions($startDate = null, $endDate = null)
    {
        $query = Order::with([
            'customer',
            'products.product',
            'services',
        ]);
        if ($startDate && $endDate) {
            $query->whereBetween('completed_date', [$startDate, $endDate]);
        }
        return $query->get();
    }

    public function showConsumptions()
    {
        $navigation = $this->navigation;
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $consumptions = $this->getPastConsumptions($startDate, $endDate);
        $customers = Customer::all();
        $products = ProductCatalog::all();
        //dd($consumptions);
        return view('stock.consumptions.past.index', compact('consumptions', 'startDate', 'endDate', 'customers', 'products', 'navigation'));
    }

    //Funcion para obtener el consumo de cada producto 


    public function getConsumptionsFiltered(Request $request)
    {
        $navigation = $this->navigation;
        $request->validate([
            'customer_id' => 'nullable|exists:customer,id',
            'product_id' => 'nullable|exists:product_catalog,id',
        ]);
        $date_range = $request->input('date_range');

        $customerId = $request->input('customer_id');
        $productId = $request->input('product_id');


        [$startDate, $endDate] = array_map(function ($d) {
            return Carbon::createFromFormat('d/m/Y', trim($d));
        }, explode(' - ', $request->input('date_range')));

        $ordersQuery = Order::with(['customer', 'products.product', 'services'])->whereBetween('programmed_date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]);

        // Consulta base

        if ($customerId) {
            $ordersQuery->where('customer_id', $customerId);
        }

        $orders = $ordersQuery->get();

        // Si hay filtro de producto, filtra los productos de cada orden
        if ($productId) {
            foreach ($orders as $order) {
                $order->products = $order->products->where('product_id', $productId)->values();
            }
            //  Elimina órdenes sin productos después del filtro
            $orders = $orders->filter(function ($order) {
                return $order->products->count() > 0;
            })->values();
        }

        $customers = Customer::all();
        $products = ProductCatalog::all();

        // dd($orders);

        return view('stock.consumptions.past.index', [
            'consumptions' => $orders,
            'date_range' => $date_range,
            'customers' => $customers,
            'products' => $products,
            'navigation' => $navigation,
        ]);
    }


    public function createRp()
    {
        $navigation = $this->navigation;
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $products = ProductCatalog::select('id', 'name')->orderBy('name')->get();
        $zones = Zone::select('id', 'name')->orderBy('name')->get();
        $rotationPlans = RotationPlan::select('id', 'name')->orderBy('name')->get();

        return view('stock.consumptions.create.order-based-rp', compact(
            'customers',
            'products',
            'zones',
            'rotationPlans',
            'navigation'
        ));
    }


    public function getProductsByPlan(Request $request)
    {
        $planId = $request->input('id');

        $products = RotationPlan::find($planId)?->products()->get(['id', 'rotation_plan_id', 'product_id', 'months'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'rotation_plan_id' => $product->rotation_plan_id,
                    'product_id' => $product->product_id,
                    'product_name' => $product->product->name ?? '',
                    'months' => json_decode($product->months, true),
                ];
            });

        //dd($products);
        return response()->json($products->values()->all());
    }

    // En tu controlador, después de generar $consumptions_data
    public function exportConsumptions(Request $request)
    {
        $consumptions_data = json_decode($request->input('data'), true); // ← Agregar true para array asociativo

        $fileName = 'consumptions_' . date('Y-m-d_His') . '.xlsx';

        // Para Excel, necesitas crear el archivo temporalmente y luego enviarlo
        $filePath = storage_path('app/temp/' . $fileName);

        // Asegurar que el directorio existe
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = SimpleExcelWriter::create($filePath);

        // Agregar encabezados primero
        $writer->addRow([
            'Nombre Producto',
            'Cantidad',
            'Métrica',
            'Nombre Cliente',
            'Zonas Comerciales',
            'Período'
        ]);

        foreach ($consumptions_data as $item) {
            // Convertir el array de zonas comerciales a string
            $comercialZones = '';
            if (isset($item['comercial_zones']) && is_array($item['comercial_zones'])) {
                $comercialZones = implode(', ', $item['comercial_zones']);
            }

            $writer->addRow([
                $item['product_name'] ?? '',
                $item['amount'] ?? 0,
                $item['product_metric'] ?? '',
                $item['customer_name'] ?? '',
                $comercialZones,
                $item['timelapse'] ?? ''
            ]);
        }

        $writer->close();

        // Devolver el archivo como descarga
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true); // ← Eliminar archivo temporal después de enviar
    }
}