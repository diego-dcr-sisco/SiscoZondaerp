<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Metric;
use App\Models\ProductCatalog;
use App\Models\Warehouse;
use App\Models\MovementProduct;

use App\Models\WarehouseMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class LotController extends Controller
{
    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Consumos en ordenes' => '/stock/movements/orders',
        //'Consumos' => '/consumptions/',
        // 'Zonas' => '/customer-zones',
        // 'Pedidos' => '/consumptions',
        // 'Productos en ordenes' => '/stock/orders-products',
        //'Estadisticas' => '/stock/analytics',
        // 'Compras' => '/purchase-requisition/purchases',
    ];

    public function index(Request $request)
    {
        // dd($request->all());
        $navigation = $this->navigation;
        $query = Lot::query()
            ->with(['product', 'warehouse'])
            ->leftJoin('product_catalog', 'lot.product_id', '=', 'product_catalog.id')
            ->select('lot.*');

        // Filtro por número de lote (registration_number)
        if ($request->filled('registration_number')) {
            $query->where('lot.registration_number', 'like', '%' . $request->registration_number . '%');
        }

        // Filtro por almacén
        if ($request->filled('warehouse')) {
            $query->where('lot.warehouse_id', $request->warehouse);
        }

        // Filtro por producto (nombre)
        if ($request->filled('product')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('lot.is_active', $request->status === 'active');
        }

        // Filtro de orden (direction)
        $direction = strtoupper($request->input('direction', 'ASC'));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        $query->orderBy('product_catalog.name', $direction)
            ->orderBy('lot.registration_number', $direction)
            ->orderBy('lot.created_at', $direction);

        // Filtro de tamaño de página (size)
        $size = $request->input('size', 50);

        $lots = $query->paginate($size);
        $products = ProductCatalog::orderBy('name', 'asc')->get();
        $metrics = Metric::all();
        $warehouses = Warehouse::orderBy('technician_id')->get();

        return view('lot.index', compact('lots', 'products', 'warehouses', 'metrics', 'navigation'));
    }

    public function searchProducts(Request $request)
    {
        try {
            $products = ProductCatalog::query()
                ->where('name', 'like', '%' . $request->q . '%')
                //->orWhere('code', 'like', '%' . $request->q . '%')
                ->select(['id', 'name'])
                ->orderBy('name', 'asc')
                //->limit(15)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $lot = new Lot();
        $lot->fill($request->all());
        $lot->end_date = $request->input('end_date') ?: $request->input('expiration_date');
        $lot->is_active = $request->boolean('is_active', true);
        $lot->save(); // Save first to generate $lot->id

        $wm = WarehouseMovement::create([
            'warehouse_id' => null,
            'destination_warehouse_id' => $request->input('warehouse_id'),
            'movement_id' => 2,
            'user_id' => Auth::id(),
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'observations' => null,
            'is_active' => true
        ]);

        // Create MovementProduct entry
        MovementProduct::create([
            'warehouse_movement_id' => $wm->id,
            'movement_id' => 2,
            'warehouse_id' => $request->input('warehouse_id'),
            'product_id' => $request->input('product_id'),
            'lot_id' => $lot->id,
            'amount' => $request->input('amount'),
        ]);

        return back();
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,ods',
            'warehouse_id' => 'required|exists:warehouse,id',
            'amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $amount = (float) ($validated['amount'] ?? 0);
        $warehouseId = (int) $validated['warehouse_id'];
        $isActive = $request->boolean('is_active', true);
        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) < 2) {
                return back()->with('error', 'El archivo no contiene filas para importar.');
            }

            $headers = $this->mapLotImportHeaders(array_shift($rows));
            $requiredHeaders = ['product_id', 'registration_number'];
            $missingHeaders = array_diff($requiredHeaders, array_keys($headers));

            if (!empty($missingHeaders)) {
                return back()->with('error', 'El archivo debe incluir PRODUCTID y NUMERO DE LOTE.');
            }

            DB::beginTransaction();

            $movement = null;
            if ($amount > 0) {
                $movement = WarehouseMovement::create([
                    'warehouse_id' => null,
                    'destination_warehouse_id' => $warehouseId,
                    'movement_id' => 2,
                    'user_id' => Auth::id(),
                    'date' => now()->format('Y-m-d'),
                    'time' => now()->format('H:i:s'),
                    'observations' => 'Importación masiva de lotes desde Excel',
                    'is_active' => true,
                ]);
            }

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;
                $productId = $this->lotImportValue($row, $headers, 'product_id');
                $productName = $this->lotImportValue($row, $headers, 'product_name');
                $registrationNumber = $this->lotImportValue($row, $headers, 'registration_number');

                if ($this->isLotImportRowEmpty($row) || (!$productId && !$productName && !$registrationNumber)) {
                    continue;
                }

                if (!$registrationNumber) {
                    $summary['skipped']++;
                    $summary['errors'][] = "Fila {$rowNumber}: falta NUMERO DE LOTE.";
                    continue;
                }

                $product = null;
                if ($productId) {
                    $product = ProductCatalog::find($productId);
                }

                if (!$product && $productName) {
                    $product = ProductCatalog::where('name', trim($productName))->first();
                }

                if (!$product) {
                    $summary['skipped']++;
                    $summary['errors'][] = "Fila {$rowNumber}: no se encontró el producto {$productId}.";
                    continue;
                }

                $productUpdates = [];
                $sanitaryRegister = $this->lotImportValue($row, $headers, 'sanitary_register');
                $activeIngredient = $this->lotImportValue($row, $headers, 'active_ingredient');

                if ($sanitaryRegister) {
                    $productUpdates['register_number'] = $sanitaryRegister;
                }

                if ($activeIngredient) {
                    $productUpdates['active_ingredient'] = $activeIngredient;
                }

                if (!empty($productUpdates)) {
                    $product->fill($productUpdates);
                    $product->save();
                }

                $lot = Lot::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('registration_number', trim($registrationNumber))
                    ->first();

                $wasRecentlyCreated = false;
                if (!$lot) {
                    $lot = new Lot();
                    $lot->product_id = $product->id;
                    $lot->warehouse_id = $warehouseId;
                    $lot->registration_number = trim($registrationNumber);
                    $lot->amount = $amount;
                    $wasRecentlyCreated = true;
                }

                $manufacturingDate = $this->parseLotImportDate($this->lotImportValue($row, $headers, 'manufacturing_date'));
                $expirationDate = $this->parseLotImportDate($this->lotImportValue($row, $headers, 'expiration_date'));

                $lot->start_date = $manufacturingDate;
                $lot->expiration_date = $expirationDate;
                $lot->end_date = $expirationDate;
                $lot->is_active = $isActive;
                $lot->save();

                if ($wasRecentlyCreated) {
                    $summary['created']++;

                    if ($movement && $amount > 0) {
                        MovementProduct::create([
                            'warehouse_movement_id' => $movement->id,
                            'movement_id' => 2,
                            'warehouse_id' => $warehouseId,
                            'product_id' => $product->id,
                            'lot_id' => $lot->id,
                            'amount' => $amount,
                        ]);
                    }
                } else {
                    $summary['updated']++;
                }
            }

            DB::commit();

            return back()
                ->with('success', "Importación completada: {$summary['created']} lotes creados, {$summary['updated']} actualizados, {$summary['skipped']} omitidos.")
                ->with('lot_import_errors', array_slice($summary['errors'], 0, 20));
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return back()->with('error', 'Error al importar lotes: ' . $e->getMessage());
        }
    }

    private function mapLotImportHeaders(array $headerRow): array
    {
        $map = [];
        $aliases = [
            'product_id' => ['productid', 'producto id', 'id producto', 'idproducto'],
            'product_name' => ['nombre del producto', 'producto', 'nombre producto'],
            'sanitary_register' => ['registro sanitario', 'registro'],
            'registration_number' => ['numero de lote', 'número de lote', 'lote', 'no lote'],
            'use' => ['uso'],
            'active_ingredient' => ['ingrediente activo'],
            'manufacturing_date' => ['fecha de fabricacion', 'fecha de fabricación', 'fabricacion'],
            'expiration_date' => ['fecha de caducidad', 'caducidad'],
        ];

        foreach ($headerRow as $column => $header) {
            $normalizedHeader = $this->normalizeLotImportHeader((string) $header);

            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalizedHeader, array_map([$this, 'normalizeLotImportHeader'], $fieldAliases), true)) {
                    $map[$field] = $column;
                    break;
                }
            }
        }

        return $map;
    }

    private function normalizeLotImportHeader(string $header): string
    {
        $header = mb_strtolower(trim($header), 'UTF-8');
        $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header);
        $header = preg_replace('/\s+/', ' ', $header);

        return trim($header);
    }

    private function lotImportValue(array $row, array $headers, string $field): ?string
    {
        if (!isset($headers[$field])) {
            return null;
        }

        $value = $row[$headers[$field]] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isLotImportRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseLotImportDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function edit($id)
    {
        $lot = Lot::findOrFail($id);
        $navigation = $this->navigation;
        $products = ProductCatalog::orderBy('name', 'asc')->get();
        $warehouses = Warehouse::where('allow_material_receipts', true)->where('is_active', true)->get();

        return view('lot.edit', compact('lot', 'products', 'warehouses', 'navigation'));
    }

    public function update(Request $request, $id)
    {
        $navigation = $this->navigation;
        $lot = Lot::findOrFail($id);
        $lot->fill($request->all());
        $lot->end_date = $request->input('end_date') ?: $request->input('expiration_date');
        $lot->is_active = $request->boolean('is_active');
        $lot->save();

        return redirect()->route('lot.index')->with('success', 'Lote actualizado satisfactoriamente')->with('navigation', $navigation);
    }

    public function toggleActive($id)
    {
        $lot = Lot::findOrFail($id);
        $lot->is_active = !$lot->is_active;
        $lot->save();

        $message = $lot->is_active
            ? 'Lote activado satisfactoriamente'
            : 'Lote ocultado satisfactoriamente';

        return back()->with('success', $message);
    }

    public function show($id)
    {
        $lot = Lot::findOrFail($id);
        $navigation = $this->navigation;
        return view('lot.show', compact('lot', 'navigation'));
    }

    public function destroy($id)
    {
        $navigation = $this->navigation;
        $lot = Lot::findOrFail($id);
        $lot->delete();

        return back();
    }

    public function getLotsByProduct(Request $request)
    {
        $navigation = $this->navigation;
        $productId = $request->query('product_id');
        $warehouseId = $request->query('warehouse_id');
        $lots = Lot::active()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('registration_number', 'asc')
            ->get();

        return response()->json($lots);
    }

    public function getTraceability($id){

        $orders = OrderProduct::with(['order.customer', 'service', 'product', 'metric', 'appMethod', 'lot'])
                ->where('lot_id', $id)
                ->get();
        $lot = Lot::find($id);
        //dd ($orders);
        return view('lot.traceability.index',compact('lot','orders'));
    }

}
