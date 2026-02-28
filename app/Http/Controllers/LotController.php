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
        $query = Lot::query()->with(['product', 'warehouse']);

        // Filtro por número de lote (registration_number)
        if ($request->filled('registration_number')) {
            $query->where('registration_number', 'like', '%' . $request->registration_number . '%');
        }

        // Filtro por almacén
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }

        // Filtro por producto (nombre)
        if ($request->filled('product')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product . '%');
            });
        }

        // Filtro de orden (direction)
        $direction = strtoupper($request->input('direction', 'DESC'));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        $query->orderBy('created_at', $direction);

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

    public function edit($id)
    {
        $lot = Lot::findOrFail($id);
        $navigation = $this->navigation;
        $products = ProductCatalog::all();
        $warehouses = Warehouse::where('allow_material_receipts', true)->where('is_active', true)->get();

        return view('lot.edit', compact('lot', 'products', 'warehouses', 'navigation'));
    }

    public function update(Request $request, $id)
    {
        $navigation = $this->navigation;
        $lot = Lot::findOrFail($id);
        $lot->fill($request->all());
        $lot->save();

        return redirect()->route('lot.index')->with('success', 'Lote actualizado satisfactoriamente')->with('navigation', $navigation);
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
        $lots = Lot::where('product_id', $productId)->where('warehouse_id', $warehouseId)->get();

        return response()->json($lots);
    }

    public function getTraceability($id){

        $orders = OrderProduct::with(['order', 'service', 'product', 'metric', 'appMethod', 'lot'])
                ->where('lot_id', $id)
                ->get();
        $lot = Lot::find($id);
        //dd ($orders);
        return view('lot.traceability.index',compact('lot','orders'));
    }

}
