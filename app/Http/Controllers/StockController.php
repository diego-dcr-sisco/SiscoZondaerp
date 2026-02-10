<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Branch;
use App\Models\IndirectProduct;
use App\Models\Metric;
use App\Models\Technician;
use App\Models\Warehouse;
use App\Models\MovementType;
use App\Models\ProductCatalog;
use App\Models\Lot;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\MovementProduct;
use App\Models\Presentation;
use App\Models\WarehouseProduct;
use App\Models\WarehouseOrder;
use Illuminate\Http\Request;
use TCPDF;

// para generar excel del stock por almacen
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\AutoFilter;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Mpdf\Tag\A;

class StockController extends Controller
{
    private $states_route = 'datas/json/Mexico_states.json';
    private $cities_route = 'datas/json/Mexico_cities.json';
    private $indirect_warehouse_name = 'SISCOPLAGAS-MRO';

    private $size = 50;

    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        // 'Zonas' => '/customer-zones',
        'Consumos en ordenes' => '/stock/movements/orders',
        'Consumos' => '/consumptions/',
        // 'Estadisticas' => '/stock/analytics',
        // 'Pedidos' => '/consumptions',
        // 'Productos en ordenes' => '/stock/orders-products',
        // 'Estadisticas' => '/stock/analytics',
        // 'Compras' => '/purchase-requisition/purchases',
    ];


    ///////////////// FUNCIONES DE ALMACENES /////////////////

    public function index(Request $request)
    {
        $user = Auth::user();
        $hasActionPermission = $user->role_id == 4 ?? false;

        $products = ProductCatalog::all();
        $branches = Branch::all();
        $lots = Lot::all();
        $metrics = Metric::all();
        $navigation = $this->navigation;

        $warehouses = Warehouse::orderBy('is_matrix', 'desc')->get();

        foreach ($warehouses as $warehouse) {
            $warehouse->products_count = $warehouse->products()->count();
        }

        $input_movements = MovementType::whereBetween('id', [1, 4])->get();
        $output_movements = MovementType::whereBetween('id', [5, 10])->get();

        $technicianIds = Warehouse::whereNotNull('technician_id')->get()->pluck('technician_id');
        $technicians = Technician::whereNotIn('id', $technicianIds)->get();

        return view('stock.index', compact(
            'warehouses',
            'hasActionPermission',
            'input_movements',
            'output_movements',
            'products',
            'lots',
            'branches',
            'technicians',
            'metrics',
            'navigation'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branch,id',
            'technician_id' => 'nullable|exists:technician,id',
            'observations' => 'nullable|string|max:1000',
            'allow_material_receipts' => 'nullable|boolean',
            'is_matrix' => 'nullable|boolean',
        ]);

        $warehouse = new Warehouse();
        $warehouse->fill($request->all());
        $warehouse->allow_material_receipts = $request->boolean('allow_material_receipts', true);
        $warehouse->is_matrix = $request->boolean('is_matrix', false);
        $warehouse->is_active = true;
        $warehouse->save();
        session()->flash('success', 'Almacén creado exitosamente');
        return redirect()->route('stock.index');
    }

    public function edit(string $id)
    {
        $navigation = $this->navigation;
        $warehouse = Warehouse::findOrFail($id);
        $branches = Branch::all();
        $technicians = Technician::all();
        $states = json_decode(file_get_contents(public_path($this->states_route)), true);
        $cities = json_decode(file_get_contents(public_path($this->cities_route)), true);

        return view('stock.edit', compact('warehouse', 'branches', 'technicians', 'states', 'cities', 'navigation'));
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branch,id',
            'technician_id' => 'nullable|exists:technician,id',
            'observations' => 'nullable|string|max:1000',
        ]);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->name = $request->name;
        $warehouse->branch_id = $request->branch_id;
        $warehouse->technician_id = $request->technician_id;

        // esto se puede optimizar pero hay que tener cuidado como 
        // esta tomando la data los checkboxes del front 

        if ($request->allow_material_receipts == '1') {
            $warehouse->allow_material_receipts = 1;
        } else {
            $warehouse->allow_material_receipts = 0;
        }

        if ($request->is_active == '1') {
            $warehouse->is_active = 1;
        } else {
            $warehouse->is_active = 0;
        }

        if ($request->is_matrix == '1') {
            $warehouse->is_matrix = 1;
        } else {
            $warehouse->is_matrix = 0;
        }

        $warehouse->observations = $request->observations;
        $warehouse->update();

        return redirect()->route('stock.index');
    }

    public function updateMovement(Request $request, string $id)
    {
        $wm = WarehouseMovement::findOrFail($id);
        $wm->update($request->all());
        return back();
    }


    public function destroy(string $id)
    {
        $navigation = $this->navigation;
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        session()->flash('success', 'Almacen eliminado correctamente');
        return redirect()->back()->with('navigation', $navigation);
    }

    ////////////////// FUNCIONES DE MOVIMIENTOS //////////////////


    public function movementsAll()
    {
        $navigation = $this->navigation;
        $warehouses = Warehouse::all();
        $movement_types = MovementType::all();
        $movements = WarehouseMovement::orderBy('date', 'DESC')->orderBy('time', 'DESC')->paginate($this->size);
        $products = ProductCatalog::whereIn('id', MovementProduct::pluck('product_id')->unique())->get();
        $lots = Lot::whereIn('product_id', $products->pluck('id')->unique())->get();


        return view('stock.movements.all', compact(
            'movements',
            'warehouses',
            'navigation',
            'movement_types',
            'products',
            'lots'
        ));
    }

    public function movementsOrders(Request $request)
    {
        $navigation = $this->navigation;
        $products = ProductCatalog::all();
        $wos = WarehouseOrder::query();

        if ($request->filled('order_folio')) {
            $wos->whereHas('order', function ($query) use ($request) {
                $query->where('folio', 'like', '%' . $request->order_folio . '%');
            });
        }

        if ($request->filled('warehouse')) {
            $warehouseName = $request->input('warehouse');
            $warehouseIds = Warehouse::where('name', 'like', '%' . $warehouseName . '%')->pluck('id');
            $wos->whereIn('warehouse_id', $warehouseIds);
        }

        if ($request->filled('technician')) {
            $technicianName = $request->input('technician');
            $technicianIds = User::where('name', 'like', '%' . $technicianName . '%')->pluck('id');
            $wos->whereIn('user_id', $technicianIds);
        }

        if ($request->filled('product_id')) {
            $wos->where('id', $request->input('product_id'));
        }

        if ($request->filled('lot_id')) {
            $wos->where('lot_id', $request->input('lot_id'));
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($date) {
                return Carbon::createFromFormat('d/m/Y', trim($date));
            }, explode(' - ', $request->date_range));

            $wos->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay()
            ]);
        }

        $wos = $wos->orderBy('created_at', $request->direction ?? 'DESC')->paginate($request->size ?? $this->size)->appends($request->all());

        $lots = Lot::with('product')->get()->sortBy('product.name');

        return view('stock.movements.order', compact('navigation', 'wos', 'products', 'lots'));
    }

    public function wMovement(string $id)
    {
        $navigation = $this->navigation;
        $movement = WarehouseMovement::findOrFail($id);

        return view('stock.movements.show.individual-movement', compact('movement', 'navigation'));
    }

    public function movementsWarehouse(Request $request, string $id)
    {
        $navigation = $this->navigation;
        $warehouse = Warehouse::findOrFail($id);
        $movements = WarehouseMovement::where('warehouse_id', $warehouse->id)->orWhere('destination_warehouse_id', $warehouse->id)->orderBy('date', 'DESC')->orderBy('time', 'DESC')->paginate($this->size);
        $products = ProductCatalog::whereIn('id', MovementProduct::pluck('product_id')->unique())->get();
        $lots = Lot::whereIn('product_id', $products->pluck('id')->unique())->get();

        return view('stock.movements.warehouse', [
            'warehouse' => $warehouse,
            'movements' => $movements,
            'movement_types' => MovementType::all(),
            'products' => $products,
            'lots' => $lots,
            'navigation' => $navigation,
            'filters' => $request->all()
        ]);
    }

    public function warehouseRecords()
    {
        dd('');
    }

    public function searchMovements(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $query = WarehouseMovement::with([
            'warehouse',
            'destinationWarehouse',
            'movementType',
            'products.product.metric',
            'products.lot'
        ])
            ->distinct()
            ->where(function ($q) use ($id) {
                $q->where('warehouse_id', $id)
                    ->orWhere(function ($subQ) use ($id) {
                        $subQ->whereNull('warehouse_id')
                            ->where('destination_warehouse_id', $id);
                    });
            });

        // Aplicar filtros (mantener tus filtros existentes)
        if ($request->filled('product_id')) {
            $query->whereHas('products', fn($q) => $q->where('product_id', $request->product_id));
        }

        if ($request->filled('lot_id')) {
            $query->whereHas('products', fn($q) => $q->where('lot_id', $request->lot_id));
        }

        // Paginar ANTES de transformar los datos
        $movements = $query->orderBy('date', $request->direction ?? 'DESC')
            ->paginate($request->size ?? $this->size);

        // Transformar solo los elementos de la página actual
        $transformed = $movements->getCollection()->map(function ($wm) {
            return $wm->products->map(function ($mp) use ($wm) {
                return [
                    'id' => $wm->id,
                    'warehouse' => $wm->warehouse->name ?? '-',
                    'destination_warehouse' => $wm->destinationWarehouse->name ?? '-',
                    'movement' => $wm->movementType->name,
                    'product' => $mp->product->name,
                    'lot' => $mp->lot->registration_number ?? '-',
                    'metric' => $mp->product->metric->value,
                    'previous_amount' => $mp->previous_amount ?? 0,
                    'amount' => $mp->amount ?? 0,
                    'date' => $wm->date,
                    'time' => $wm->time
                ];
            });
        })->collapse()->sortByDesc('date')->sortByDesc('time');

        // Reemplazar la colección en el paginador
        $movements->setCollection($transformed);

        return view('stock.movements.warehouse', [
            'movements' => $movements,
            'warehouse' => $warehouse,
            'movement_types' => MovementType::all(),
            'products' => ProductCatalog::whereIn('id', $transformed->pluck('product_id')->unique())->get(),
            'lots' => Lot::whereIn('id', $transformed->pluck('lot_id')->filter()->unique())->get(),
            'navigation' => $this->navigation,
            'filters' => $request->all()
        ]);
    }

    // Funcion hecha por Diego para mostrar los productos en ordenes
    // No se va a utilizar en el proyecto por cambio en tablas de datos 'warehouse_lot' 
    // public function showWarehouseProductOrder()
    // {
    //     $navigation = $this->navigation;
    //     $warehouse = Warehouse::findOrFail(1);
    //     $movements = WarehouseProductOrder::where('is_active', true)
    //         ->where('warehouse_id', $warehouse->id)
    //         ->paginate(50);
    //     return view('stock.show.product-orders', compact('movements', 'navigation'));
    // }

    /*public function movement_print(string $id)
    {
        $navigation = $this->navigation;
        $movement = WarehouseMovement::with(['warehouse', 'destinationWarehouse', 'user', 'movementType'])
            ->where('id', $id)
            ->first();
        //almacen de donde se realizo el movimiento
        $warehouse = Warehouse::findOrFail($movement->warehouse_id);
        $products = MovementProduct::where('movement_id', $id)->get();
        //dd($products);
        $pdf = new TCPDF();
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);

        // Establece la información del documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Siscoplagas');
        $pdf->SetTitle('Movimiento de almacén');

        // Añade una página
        $pdf->AddPage();

        // Añadir header personalizado
        $this->addCustomHeader($pdf, $movement->date, $movement->time, $movement->id);

        // Márgenes
        $margin = 10;
        $heightPage = $pdf->getPageHeight() - ($margin * 2);
        $widthPage = $pdf->getPageWidth() - ($margin * 2);

        // Configura posición y tamaño
        $x = $margin;
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + $margin);
        $y += 10;
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        // Establece fuente y color de fondo para los títulos
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(255, 255, 255);
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + $margin);
        // Datos del Movimiento
        $pdf->MultiCell(0, 0, 'Datos del Movimiento', 0, 'L', 0, 1, $x, $y);
        $y += 10;
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 0, "EMPLEADO: " . $movement->user->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "FECHA: " . $movement->date, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        if ($movement->movement_type_id >= 1 && $movement->movement_type_id <= 5) {
            $pdf->MultiCell(0, 0, "E/S: Entrada", 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "E/S: Salida", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        $pdf->MultiCell(0, 0, "TIPO: " . $movement->movementType->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "ALMACÉN: " . $movement->warehouse->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        if ($warehouse->source_warehouse_id) {
            $pdf->MultiCell(0, 0, "BLOQUEADO: SI", 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "BLOQUEADO: NO", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        if ($movement->destination_warehouse_id) {
            $pdf->MultiCell(0, 0, "ALMACÉN DE DESTINO: " . $movement->destinationWarehouse->name, 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "ALMACÉN DE DESTINO: No aplica", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        $pdf->MultiCell(0, 0, "COMENTARIOS: " . $movement->remarks, 0, 'L', 0, 1, $x, $y);
        $pdf->SetDrawColor(0, 0, 0); // RGB para negro
        $y += 10;

        // Incrementar la posición Y para la siguiente línea de texto
        $y += 5;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 0, 'Listado de productos', 0, 'L', 0, 1, $x, $y);
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        // Espacio para separar secciones
        $y += 10;
        $pdf->SetDrawColor(117, 170, 220); // RGB para azul

        // Definir el ancho de las celdas
        $cellWidth = $widthPage / 5;
        $pdf->Ln();
        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($cellWidth, 7, 'Producto', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Cantidad', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Tipo', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Lote', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Fecha de Caducidad', 1, 1, 'C', 0);
        $pdf->SetFont('helvetica', '', 9);
        foreach ($products as $product) {
            $pdf->Cell($cellWidth, 7, $product->product->name, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->amount . ' ' . $product->product->metric->value, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $movement->movementType->name, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->lot->registration_number, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->lot->expiration_date ?? '-', 1, 1, 'C', 0); // Salto de línea para la siguiente fila
            $y += 7; // Añadir altura de la fila a la posición Y
        }

        $pdf->SetDrawColor(0, 0, 0); // RGB para negro
        $y += 10;

        // Registros de auditoría
        $y += 5;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 0, 'Registros de auditoría', 0, 'L', 0, 1, $x, $y);
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        $y += 5;
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 0, "Usuario: " . $movement->user->name, 0, 'L', 0, 1, $x, $y);

        $y += 5;
        $pdf->MultiCell(0, 0, "Correo: " . $movement->user->email, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "Fecha: " . $movement->date, 0, 'L', 0, 1, $x, $y);
        $y += 5;

        // Salida del PDF
        $pdf->Output('movimiento_almacen.pdf', 'I');

        // Envía el PDF al navegador
        $pdf->Output('ejemplo_tcpdf.pdf', 'I');
    }*/
    // Método para el header personalizado
    private function addCustomHeader($pdf, $date, $time, $idm)
    {
        $margin = 10;
        $heightPage = $pdf->getPageHeight() - ($margin * 2);
        $widthPage = $pdf->getPageWidth() - ($margin * 2);

        // Establecer la posición del header
        $pdf->SetY($margin); // Posición desde arriba
        $pdf->SetX($margin); // Posición desde la izquierda

        // Establecer fuente para el header
        $pdf->SetFont('helvetica', 'B', 12);

        // Agregar texto al header
        $pdf->Cell(0, 10, 'Fecha: ' . $date . ' Hora: ' . $time . '    Movimiento de Almacen: ' . $idm, 0, 1, 'L');



        // Configura la posición para la imagen
        $imageWidth = 30;  // Ancho de la imagen
        $imageHeight = 10; // Alto de la imagen
        $imageX = $margin;  // Coordenada X para la imagen
        $imageY = $pdf->GetY(); // Coordenada Y para la imagen (justo debajo del texto)

        // Ruta de la imagen
        $imagePath = public_path('images/logo.png');

        // Agregar la imagen
        $pdf->Image($imagePath, $imageX, $imageY, $imageWidth, $imageHeight, 'PNG');
    }

    public function analytics()
    {
        $navigation = $this->navigation;

        // no se usa $data, por ahora solo $charts
        $data = [];
        $data['product_use'] = (new GraphicController)->productUse();
        $data['stock_movements'] = (new GraphicController)->stockMovements();
        $data['domestic'] = (new GraphicController)->orderTypes(1);
        $data['comercial'] = (new GraphicController)->orderTypes(2);

        $charts = [
            'product_use' => (new GraphicController)->productUse(),
            'stock_movements' => (new GraphicController)->stockMovements(),
            //'domestic' => (new GraphicController)->orderTypes(1),
            //'comercial' => (new GraphicController)->orderTypes(2),
        ];

        $products = ProductCatalog::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('stock.analytics.index', compact('charts', 'products', 'warehouses', 'navigation'));
    }

    ///////////////////////////////// FUNCIONES PARA MOSTRAR EL STOCK DEL ALMACEN /////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function showProducts($id)
    {
        $products_data = [];
        $navigation = $this->navigation;
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            abort(404, 'Almacén no encontrado');
        }

        $products = MovementProduct::getProductsGroupedByLot($warehouse->id);

        foreach ($products as $lot_id => $product_data) {
            foreach ($product_data as $data) {
                $presentation = Presentation::find($data['product']['presentation_id']);
                $products_data[] = [
                    'product' => $data['product']['name'] ?? '-',
                    'presentation' => $presentation['name'] ?? '-',
                    'lot' => $data['lot']['registration_number'] ?? '-',
                    'amount' => $data['amount']['net'],
                    'metric' => $data['product']['metric']['value'] ?? '-',
                    'expiration_date' => $data['lot']['expiration_date'] ?? '-'
                ];
            }
        }

        return view(
            'stock.show.products',
            compact('warehouse', 'products_data', 'navigation')
        );
    }

    public function show(string $id)
    {
        $navigation = $this->navigation;
        $warehouse = Warehouse::with('branch','technician.user')->findOrFail($id);

        $rows = MovementProduct::where('warehouse_id', $id)
            ->selectRaw('lot_id, product_id, SUM(CASE WHEN movement_id BETWEEN 1 AND 4 THEN amount ELSE 0 END) as add_amount, SUM(CASE WHEN movement_id BETWEEN 5 AND 10 THEN amount ELSE 0 END) as less_amount')
            ->with(['product.metric', 'lot'])
            ->groupBy('lot_id','product_id')
            ->get();

        $stocks = $rows->map(function($item) {
            $net = ($item->add_amount ?? 0) - ($item->less_amount ?? 0);
            return (object) [
                'id' => $item->lot->id ?? $item->product_id,
                'product' => $item->product,
                'amount' => $net,
                'add_amount' => $item->add_amount ?? 0,
                'less_amount' => $item->less_amount ?? 0,
                'registration_number' => $item->lot->registration_number ?? '-',
            ];
        });

        $stockTotals = [
            'rows' => $rows->count(),
            'distinct_products' => $rows->pluck('product_id')->unique()->count(),
            'distinct_lots' => $rows->pluck('lot_id')->unique()->count(),
            'total_net' => $rows->reduce(function($carry, $item) {
                return $carry + (($item->add_amount ?? 0) - ($item->less_amount ?? 0));
            }, 0)
        ];

        $query_variables = [
            'select' => "lot_id, product_id, SUM(CASE WHEN movement_id BETWEEN 1 AND 4 THEN amount ELSE 0 END) as add_amount, SUM(CASE WHEN movement_id BETWEEN 5 AND 10 THEN amount ELSE 0 END) as less_amount",
            'groupBy' => ['lot_id','product_id']
        ];

        return view('stock.show', compact('warehouse', 'stocks', 'rows', 'navigation', 'stockTotals', 'query_variables'));
    }

    // Generar archivo de excel con los productos
    public function exportStock($id)
    {
        $navigation = $this->navigation;
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            abort(404, 'Almacén no encontrado');
        }

        // Obtener productos agrupados por lote y producto con cantidades netas
        $rows = MovementProduct::where('warehouse_id', $id)
            ->selectRaw('lot_id, product_id, SUM(CASE WHEN movement_id BETWEEN 1 AND 4 THEN amount ELSE 0 END) as add_amount, SUM(CASE WHEN movement_id BETWEEN 5 AND 10 THEN amount ELSE 0 END) as less_amount')
            ->with(['product.presentation','product.metric','lot'])
            ->groupBy('lot_id','product_id')
            ->get();

        // propiedades del archivo Excel
        $properties = new Properties(
            title: 'Productos en almacen - ' . Carbon::now()->format('d-m-Y')
        );
        $options = new Options();
        $options->setProperties($properties);

        // Crear el archivo Excel
        $writer = new Writer($options);
        $filePath = storage_path(
            'app/public/productos_almacen_' . $warehouse->name . Carbon::now()->format('d-m-Y') . '.xlsx'
        );
        $writer->openToFile($filePath);


        // Estilo para los encabezados
        $headerStyle = (new Style())
            ->setBackgroundColor(Color::BLUE)
            ->setFontColor(Color::WHITE)
            ->setFontSize(14)
            ->setFontBold();
        $headers = ['#', 'Producto', 'Presentación', 'lote', 'Cantidad', 'Caducidad'];
        $autoFilter = new AutoFilter(0, 1, count($headers) - 1, 1048576);
        $writer->getCurrentSheet()->setAutoFilter($autoFilter);

        $writer->addRow(Row::fromValues($headers, $headerStyle));

        // Escribir los datos de los productos (cantidad neta por lote/producto)
        foreach ($rows as $index => $item) {
            $net = ($item->add_amount ?? 0) - ($item->less_amount ?? 0);
            $rowData = [
                $index + 1, // Número de fila
                $item->product->name ?? '-', // Nombre del producto
                $item->product->presentation ? $item->product->presentation->name : '-',
                $item->lot ? $item->lot->registration_number : '-', // Lote (o '-' si no hay lote)
                $net . ' ' . ($item->product->metric->value ?? '-'), // Cantidad con métrica
                $item->lot->expiration_date ?? '-'
            ];
            $writer->addRow(Row::fromValues($rowData));
        }

        // Cerrar el escritor
        $writer->close();

        // Descargar el archivo
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    ////////////////////////////////////////////// FUNCIONES PARA MOVIMIENTOS /////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Funciones para movimientos de almacen

    public function entry(string $id)
    {
        $products_data = [];
        $navigation = $this->navigation;
        $warehouse = Warehouse::find($id);
        $all_warehouses = Warehouse::where('id', '!=', $id)->get();
        $products = $warehouse->products();//ProductCatalog::all();
        $input_movements = MovementType::whereBetween('id', [1, 4])->get();

        foreach ($products as $product) {
            dd($product);
            $products_data[] = [
                'id' => $product->id,
                'name' => $product->name,
                'presentation' => $product->presentation ? $product->presentation->name : '-',
                'metric' => $product->metric ? $product->metric->value : '-',
                'lots' => $product->lots->map(function ($lot) use ($warehouse) {
                    $current_amount = $lot->countProductsByWarehouse($warehouse->id);
                    return [
                        'id' => $lot->id,
                        'registration_number' => $lot->registration_number,
                        'amount' => $lot->amount,
                        'current_amount' => $current_amount ?? 0,
                    ];
                })->toArray(),
            ];
        }

        session()->flash('warning', 'Antes de agregar un movimiento de entrada, asegurate de haber registrado los lotes correspondientes en el almacén.');

        return view('stock.create.inputs.entries', compact('warehouse', 'all_warehouses', 'products_data', 'input_movements', 'navigation'));
    }

    // Salidas de almacen
    public function exits(string $id)
    {
        $products_data = [];
        $navigation = $this->navigation;
        $warehouse = Warehouse::find($id);
        $all_warehouses = Warehouse::where('id', '!=', $id)->get();
        $products = $warehouse->products();
        $output_movements = MovementType::whereBetween('id', [5, 10])->get();

        foreach ($products as $product) {
            $products_data[] = [
                'id' => $product->id,
                'name' => $product->name,
                'presentation' => $product->presentation ? $product->presentation->name : '-',
                'metric' => $product->metric ? $product->metric->value : '-',
                'lots' => $product->lots->map(function ($lot) use ($warehouse) {
                    $current_amount = $lot->countProductsByWarehouse($warehouse->id);
                    return [
                        'id' => $lot->id,
                        'registration_number' => $lot->registration_number,
                        'amount' => $lot->amount,
                        'current_amount' => $current_amount ?? 0,
                    ];
                })->toArray(),
            ];
        }

        session()->flash('warning', 'Antes de agregar un movimiento de salida, asegurate de haber registrado los lotes correspondientes en el almacén.');

        return view('stock.create.outputs.exits', compact('warehouse', 'all_warehouses', 'products_data', 'output_movements', 'navigation'));
    }


    public function storeInMovement(Request $request)
    {
        dd($request->all());
        $products = json_decode($request->input('products'), true);
        $movement_id = $request->input('movement_id');


        // Crear el movimiento principal
        $wm = WarehouseMovement::create([
            'warehouse_id' => $request->input('warehouse_id'),
            'destination_warehouse_id' => $request->input('destination_warehouse_id'),
            'movement_id' => $movement_id,
            'description' => $request->input('description'),
            'date' => $request->input['date'] ?? now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'user_id' => Auth::id(),
            'warehouse_signature' => $request->input('storekeeper_signature_base64'),
            'technician_signature' => $request->input('technician_signature_base64')
        ]);

        // Procesar cada producto
        foreach ($products as $product) {
            // 3. Registrar el producto en el movimiento
            MovementProduct::create([
                'warehouse_movement_id' => $wm->id,
                'movement_id' => $movement_id,
                'warehouse_id' => $wm->destination_warehouse_id,
                'product_id' => $product['product_id'],
                'lot_id' => $product['lot_id'],
                'amount' => $product['amount'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->route('stock.index')
            ->with('success', 'Movimiento de entrada registrado exitosamente');
    }


    public function storeOutMovement(Request $request)
    {
        //dd($request->all());
        $products = json_decode($request->input('products'), true);
        $movement_id = $request->input('movement_id');

        $wm = WarehouseMovement::create([
            'warehouse_id' => $request->input('warehouse_id'),
            'destination_warehouse_id' => $request->input('destination_warehouse_id'),
            'movement_id' => $movement_id,
            'description' => $request->input('description'),
            'date' => $request->input['date'] ?? now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'user_id' => Auth::id(),
            'warehouse_signature' => $request->warehouse_signature,
            'technician_signature' => $request->technician_signature
        ]);


        // Procesar cada producto
        foreach ($products as $product) {
            MovementProduct::create([
                'warehouse_movement_id' => $wm->id,
                'movement_id' => $movement_id,
                'warehouse_id' => $wm->warehouse_id,
                'product_id' => $product['product_id'],
                'lot_id' => $product['lot_id'],
                'amount' => $product['amount'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!$wm->destination_warehouse_id) {
                continue;
            }

            if ($wm->movement_id == 7) {
                MovementProduct::create([
                    'warehouse_movement_id' => $wm->id,
                    'movement_id' => 3,
                    'warehouse_id' => $wm->destination_warehouse_id,
                    'product_id' => $product['product_id'],
                    'lot_id' => $product['lot_id'],
                    'amount' => $product['amount'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return redirect()->route('stock.index')
            ->with('success', 'Movimiento de salida registrado exitosamente');
    }

    // funcion para actualizar la cantidad del lote en el almacen
    public function updateLots($origin_warehouse, $destination_warehouse, $products, int $type)
    {
        // origin warehouse es de donde salen los productos
        // destination warehouse es a donde entran los productos
        // $products = json_decode($products, true);

        switch ($type) {
            case 0:
                // si es una entrada, puede que no haya almacen de origen, solo destino
                $origin = $origin_warehouse ? Warehouse::find($origin_warehouse) : null;
                $destination = Warehouse::find($destination_warehouse);

                foreach ($products as $product) {
                    $warehouse_product = WarehouseProduct::find($product->warehouse_product_id);
                    $destination_lot = Lot::where('id', $warehouse_product->lot_id)
                        ->where('warehouse_id', $destination->id)
                        ->first();

                    $destination_lot->amount += $product->amount;
                    $destination_lot->save();

                    // Tambien se modifica la cantidad en la tabla warehouse products

                    $warehouse_product->amount += $product->amount;
                    $warehouse_product->save();

                    if ($origin) {
                        $origin_lot = Lot::where('id', $warehouse_product->lot_id)
                            ->where('warehouse_id', $origin->id)
                            ->first();
                        if ($origin_lot) {
                            $origin_lot->amount -= $product->amount;
                            $origin_lot->save();
                        }

                        $origin_warehouse_product = WarehouseProduct::where('product_id', $warehouse_product->product_id)
                            ->where('lot_id', $warehouse_product->lot_id)
                            ->where('warehouse_id', $origin->id)
                            ->first();
                        if ($origin_warehouse_product) {
                            $origin_warehouse_product->amount -= $product->amount;
                            $origin_warehouse_product->save();
                        }

                    }
                }
                break;
            case 1:
                $origin = Warehouse::find($origin_warehouse);
                $destination = Warehouse::find($destination_warehouse);
                // dd($origin, $destination);

                // si es una salida, puede que el lote no exista en el almacen a donde va a llegar
                foreach ($products as $product) {
                    // dd($product->warehouse_product_id, $origin->id, $product->lot_id);
                    $warehouse_product = WarehouseProduct::where('product_id', $product->warehouse_product_id)
                        ->where('warehouse_id', $origin->id)
                        ->first();
                    $warehouse_product->amount -= $product->amount;
                    $warehouse_product->save();

                    $origin_lot = Lot::where('id', $warehouse_product->lot_id)
                        ->where('warehouse_id', $origin->id)
                        ->first();
                    $origin_lot->amount -= $product->amount;
                    $origin_lot->save();

                    $destination_lot = Lot::where('id', $warehouse_product->lot_id)
                        ->where('warehouse_id', $destination->id)
                        ->first();

                    // si el lote de destino existe le suma la cantidad,si no lo crea 
                    if ($destination_lot) {
                        $destination_lot->amount += $product->amount;
                        $destination_lot->save();

                        $destination_warehouse_product = WarehouseProduct::where('product_id', $warehouse_product->product_id)
                            ->where('lot_id', $warehouse_product->lot_id)
                            ->where('warehouse_id', $destination->id)
                            ->first();
                        if (!$destination_warehouse_product) {
                            $destination_warehouse_product = new WarehouseProduct();
                            $destination_warehouse_product->product_id = $warehouse_product->product_id;
                            $destination_warehouse_product->lot_id = $warehouse_product->lot_id;
                            $destination_warehouse_product->warehouse_id = $destination->id;
                            $destination_warehouse_product->save();
                        }
                        $destination_warehouse_product->amount += $product->amount;
                        $destination_warehouse_product->save();

                    } else {
                        //comentado por que aun no se sabe si es buena idea crearlo o no

                        // Lot::create([
                        //     'product_id' => $product['product_id'],
                        //     'warehouse_id' => $destination->id,
                        //     'registration_number' => $origin_lot->registration_number,
                        //     'expiration_date' => $origin_lot->expiration_date,
                        //     'amount' => $product['amount'],
                        //     'start_date' => $origin_lot->start_date,
                        //     'end_date' => $origin_lot->end_date,
                        //     'created_at' => now(),
                        //     'updated_at' => now()
                        // ]);
                    }
                }
                break;
        }

    }

    // funcion para mostrar el registro de movimientos por lote 
    public function movementTimeline(string $id)
    {
        $navigation = $this->navigation;
        $lot = Lot::find($id);
        $warehouses = Warehouse::all();

        $movements = $lot->movements->map(function ($movementProduct) {
            return $movementProduct->movement;
        })->unique('id')->values();

        // Paginar los movimientos a 25 por página
        $perPage = 25;
        $currentPage = request()->input('page', 1);
        $pagedMovements = new \Illuminate\Pagination\LengthAwarePaginator(
            $movements->forPage($currentPage, $perPage),
            $movements->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        foreach ($pagedMovements as $movement) {
            $movement->products = $movement->getProducts;
        }
        $movements = $pagedMovements;

        return view('stock.movements.show.lot-timeline', compact('lot', 'warehouses', 'movements', 'navigation'));
    }

    // funcion para revertir un movimiento si se necesita
    public function revertMovement($id)
    {
        $navigation = $this->navigation;
        DB::beginTransaction();

        try {
            $movement = WarehouseMovement::with('movementProducts.product.lot')->findOrFail($id);

            // Verificar si el movimiento ya fue revertido
            if (!$movement->is_active) {
                session()->flash('warning', 'Este movimiento ya fue revertido anteriormente.');
                return back();
            }

            // Determinar si es entrada (1-4) o salida (5-10)
            $isEntry = $movement->movement_id <= 4;

            foreach ($movement->movementProducts as $movementProduct) {
                $warehouseProduct = $movementProduct->product;
                $lot = $warehouseProduct->lot;
                $amount = $movementProduct->amount;

                // Para movimientos de entrada (revertir = sacar del destino)
                if ($isEntry) {
                    // Restaurar cantidad en almacén origen si existe
                    if ($movement->origin_warehouse_id) {
                        $originLot = Lot::where('product_id', $warehouseProduct->product_id)
                            ->where('warehouse_id', $movement->origin_warehouse_id)
                            ->where('id', $lot->id)
                            ->first();

                        if ($originLot) {
                            $originLot->amount += $amount;
                            $originLot->save();
                        }
                    }

                    // Quitar cantidad del almacén destino
                    $destinationLot = Lot::where('product_id', $warehouseProduct->product_id)
                        ->where('warehouse_id', $movement->destination_warehouse_id)
                        ->where('id', $lot->id)
                        ->first();

                    if ($destinationLot) {
                        $destinationLot->amount -= $amount;
                        // Eliminar el lote si la cantidad llega a cero
                        if ($destinationLot->amount <= 0) {
                            $destinationLot->delete();
                        } else {
                            $destinationLot->save();
                        }
                    }
                }
                // Para movimientos de salida (revertir = devolver al origen)
                else {
                    // Quitar cantidad del almacén origen
                    $originLot = Lot::where('product_id', $warehouseProduct->product_id)
                        ->where('warehouse_id', $movement->warehouse_id)
                        ->where('id', $lot->id)
                        ->first();

                    if ($originLot) {
                        $originLot->amount += $amount;
                        $originLot->save();
                    }

                    // Restaurar cantidad en almacén destino si existe
                    if ($movement->destination_warehouse_id) {
                        $destinationLot = Lot::where('product_id', $warehouseProduct->product_id)
                            ->where('warehouse_id', $movement->destination_warehouse_id)
                            ->where('id', $lot->id)
                            ->first();

                        if ($destinationLot) {
                            $destinationLot->amount -= $amount;
                            // Eliminar el lote si la cantidad llega a cero
                            if ($destinationLot->amount <= 0) {
                                $destinationLot->delete();
                            } else {
                                $destinationLot->save();
                            }
                        }
                    }
                }

                // Actualizar el warehouse_product relacionado
                $warehouseProduct->amount = $movementProduct->previous_amount;
                $warehouseProduct->save();
            }

            // Marcar el movimiento como inactivo
            $movement->is_active = 0;
            $movement->save();

            DB::commit();

            session()->flash('success', 'Movimiento revertido exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al revertir el movimiento: ' . $e->getMessage());
            Log::error('Error reverting movement: ' . $e->getMessage());
        }

        return back()->with('navigation', $navigation);
    }

    // Funcion para generar un excel de los movimientos mostrados
    public function exportMovements(Request $request)
    {
        $navigation = $this->navigation;
        // Replicamos la misma lógica de filtrado que en allMovements()
        $query = WarehouseMovement::with(['warehouse', 'destinationWarehouse', 'movementType'])
            ->where('is_active', true);

        if ($request->filled('movement_type')) {
            if ($request->movement_type == 'entrada') {
                $query->whereBetween('movement_id', [1, 4]);
            } elseif ($request->movement_type == 'salida') {
                $query->whereBetween('movement_id', [5, 10]);
            }
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('destination_warehouse_id')) {
            $query->where('destination_warehouse_id', $request->destination_warehouse_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $movements = $query->get();

        // Configuración del archivo Excel
        $properties = new Properties(
            title: 'Reporte de Movimientos - ' . Carbon::now()->format('d-m-Y')
        );
        $options = new Options();
        $options->setProperties($properties);

        $writer = new Writer($options);
        $fileName = 'movimientos_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        $writer->openToFile($filePath);

        $wrapTextStyle = (new Style())
            ->setShouldWrapText(true); // Habilita el ajuste de texto

        // Estilo para encabezados
        $headerStyle = (new Style())
            ->setBackgroundColor(Color::BLUE)
            ->setFontColor(Color::WHITE)
            ->setFontSize(12)
            ->setFontBold();

        // Encabezados
        $headers = [
            'ID',
            'Fecha',
            'Hora',
            'Tipo Movimiento',
            'Entrada/Salida',
            'Almacén Origen',
            'Almacén Destino',
            'Productos',
            'Usuario',
            'Observaciones'
        ];

        $autoFilter = new AutoFilter(0, 1, count($headers) - 1, 1048576);
        $writer->getCurrentSheet()->setAutoFilter($autoFilter);
        $writer->addRow(Row::fromValues($headers, $headerStyle));

        // Datos
        $query->chunk(1000, function ($movements) use ($writer, $wrapTextStyle) {
            foreach ($movements as $movement) {
                $rowData = [
                    $movement->id,
                    $movement->date,
                    $movement->time,
                    $movement->movementType ? $movement->movementType->name : '-',
                    ($movement->movement_id <= 4) ? 'Entrada' : 'Salida',
                    $movement->warehouse ? $movement->warehouse->name : '-',
                    $movement->destinationWarehouse ? $movement->destinationWarehouse->name : '-',
                    $this->productsInMovement($movement),
                    $movement->user ? $movement->user->name : '-',
                    $movement->observations ?? '-'
                ];

                $row = Row::fromValues($rowData);
                $row->setStyle($wrapTextStyle);
                $writer->addRow(Row::fromValues($rowData));
            }
        });

        $writer->close();

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    private function productsInMovement($movement)
    {
        $products = $movement->getProducts;
        // dd($products);
        $productDetails = [];
        foreach ($products as $product) {
            $productDetails[] = $product->product->product->name . ' - ' . $product->amount . ' uds';
        }

        return implode("\n", $productDetails);
    }

    ////////////////////////////////////////////// FUNCIONES PARA ALMACEN INDIRECTOS //////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getIndirectWarehouse(string $name)
    {
        $warehouse = Warehouse::where('name', $name)->first();
        if (!$warehouse) {
            $warehouse = new Warehouse();
            $warehouse->name = $this->indirect_warehouse_name;
            $warehouse->branch_id = 1;
            $warehouse->technician_id = null;
            $warehouse->allow_material_receipts = 1;
            $warehouse->is_active = 1;
            $warehouse->is_matrix = 1;
            $warehouse->observations = 'Almacén de productos misceláneos, epp, herramientas, insumos de oficina, etc. para requisiciones de la misma empresa.';
            $warehouse->save();
        }

        return $warehouse;
    }

    public function indirectWarehouse()
    {
        $navigation = $this->navigation;
        $warehouse = $this->getIndirectWarehouse($this->indirect_warehouse_name);
        $indirect_warehouse_id = $warehouse->id;
        $newProducts = IndirectProduct::where('base_stock', null)->get();
        $products = IndirectProduct::where('base_stock', '!=', null)->paginate(30);
        $navigation = $this->navigation;

        return view('stock.indirect', compact('warehouse', 'indirect_warehouse_id', 'newProducts', 'products', 'navigation'));
    }

    public function storeIndirectProduct(Request $request, $id)
    {
        $product = IndirectProduct::find($request->id);
        $product->description = $request->description;
        $product->base_stock = $request->base_stock ?? 0;
        if ($product->code != $request->code) {
            $product->code = $request->code;
        }

        $product->save();
        session()->flash('success', 'Producto agregado al almacén');
        return redirect()->back()->with('navigation', $this->navigation);
    }

    public function updateIndirectProduct(Request $request, $id)
    {
        $navigation = $this->navigation;
        $product = IndirectProduct::find($id);
        $product->description = $request->description;
        $product->code = $request->code;
        $product->base_stock = $request->base_stock;
        $product->save();
        session()->flash('success', 'Producto actualizado correctamente');
        return redirect()->back()->with('navigation', $this->navigation);
    }

    public function destroyIndirectProduct($id)
    {
        $navigation = $this->navigation;
        $product = IndirectProduct::find($id);
        $product->delete();
        session()->flash('success', 'Producto eliminado correctamente');
        return redirect()->back()->with('navigation', $navigation);
    }

    public function movement_print(string $id)
    {
        $movement = WarehouseMovement::with(['warehouse', 'destinationWarehouse', 'user', 'movementType'])
            ->where('id', $id)
            ->first();
        //almacen de donde se realizo el movimiento
        $warehouse = Warehouse::findOrFail($movement->warehouse_id);
        $products = MovementProduct::where('movement_id', $id)->get();
        //dd($products);
        $pdf = new TCPDF();
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);

        // Establece la información del documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Siscoplagas');
        $pdf->SetTitle('Movimiento de almacén');

        // Añade una página
        $pdf->AddPage();

        // Añadir header personalizado
        $this->addCustomHeader($pdf, $movement->date, $movement->time, $movement->id);

        // Márgenes
        $margin = 10;
        $heightPage = $pdf->getPageHeight() - ($margin * 2);
        $widthPage = $pdf->getPageWidth() - ($margin * 2);

        // Configura posición y tamaño
        $x = $margin;
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + $margin);
        $y += 10;
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        // Establece fuente y color de fondo para los títulos
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(255, 255, 255);
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y + $margin);
        // Datos del Movimiento
        $pdf->MultiCell(0, 0, 'Datos del Movimiento', 0, 'L', 0, 1, $x, $y);
        $y += 10;
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 0, "EMPLEADO: " . $movement->user->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "FECHA: " . $movement->date, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        if ($movement->movement_type_id >= 1 && $movement->movement_type_id <= 5) {
            $pdf->MultiCell(0, 0, "E/S: Entrada", 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "E/S: Salida", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        $pdf->MultiCell(0, 0, "TIPO: " . $movement->movementType->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "ALMACÉN: " . $movement->warehouse->name, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        if ($warehouse->source_warehouse_id) {
            $pdf->MultiCell(0, 0, "BLOQUEADO: SI", 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "BLOQUEADO: NO", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        if ($movement->destination_warehouse_id) {
            $pdf->MultiCell(0, 0, "ALMACÉN DE DESTINO: " . $movement->destinationWarehouse->name, 0, 'L', 0, 1, $x, $y);
        } else {
            $pdf->MultiCell(0, 0, "ALMACÉN DE DESTINO: No aplica", 0, 'L', 0, 1, $x, $y);
        }
        $y += 5;
        $pdf->MultiCell(0, 0, "COMENTARIOS: " . $movement->remarks, 0, 'L', 0, 1, $x, $y);
        $pdf->SetDrawColor(0, 0, 0); // RGB para negro
        $y += 10;

        // Incrementar la posición Y para la siguiente línea de texto
        $y += 5;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 0, 'Listado de productos', 0, 'L', 0, 1, $x, $y);
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        // Espacio para separar secciones
        $y += 10;
        $pdf->SetDrawColor(117, 170, 220); // RGB para azul

        // Definir el ancho de las celdas
        $cellWidth = $widthPage / 5;
        $pdf->Ln();
        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($cellWidth, 7, 'Producto', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Cantidad', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Tipo', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Lote', 1, 0, 'C', 0);
        $pdf->Cell($cellWidth, 7, 'Fecha de Caducidad', 1, 1, 'C', 0);
        $pdf->SetFont('helvetica', '', 9);
        foreach ($products as $product) {
            $pdf->Cell($cellWidth, 7, $product->product->name, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->amount . ' ' . $product->product->metric->value, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $movement->movementType->name, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->lot->registration_number, 1, 0, 'C', 0);
            $pdf->Cell($cellWidth, 7, $product->lot->expiration_date ?? '-', 1, 1, 'C', 0); // Salto de línea para la siguiente fila
            $y += 7; // Añadir altura de la fila a la posición Y
        }

        $pdf->SetDrawColor(0, 0, 0); // RGB para negro
        $y += 10;

        // Registros de auditoría
        $y += 5;
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(0, 0, 'Registros de auditoría', 0, 'L', 0, 1, $x, $y);
        // Establecer grosor de la línea
        $pdf->SetLineWidth(0.5); // Grosor de la línea en mm
        // Establecer el color de la línea (por ejemplo, azul)
        $pdf->SetDrawColor(133, 141, 72); // RGB para azul

        // Establecer el grosor de la línea (más delgada)
        $pdf->SetLineWidth(0.25); // Grosor de la línea en mm

        // Dibujar una línea horizontal
        $xStart = $margin; // Coordenada X de inicio
        $yStart = $y += 5; // Coordenada Y de inicio
        $xEnd = $pdf->getPageWidth() - 10; // Coordenada X de fin
        $yEnd = $y; // Coordenada Y de fin (misma que la inicial para una línea horizontal)

        $pdf->Line($xStart, $yStart, $xEnd, $yEnd);

        $y += 5;
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 0, "Usuario: " . $movement->user->name, 0, 'L', 0, 1, $x, $y);

        $y += 5;
        $pdf->MultiCell(0, 0, "Correo: " . $movement->user->email, 0, 'L', 0, 1, $x, $y);
        $y += 5;
        $pdf->MultiCell(0, 0, "Fecha: " . $movement->date, 0, 'L', 0, 1, $x, $y);
        $y += 5;

        // Salida del PDF
        $pdf->Output('movimiento_almacen.pdf', 'I');

        // Envía el PDF al navegador
        $pdf->Output('ejemplo_tcpdf.pdf', 'I');
    }

    public function voucherPdfPreview($id)
    {
        try {
            $data = [];
            $technian_name = 'No asignado';
            $movement = WarehouseMovement::with(['user', 'warehouse', 'destinationWarehouse', 'movement'])->findOrFail($id);

            // Procesar firma del almacenista si existe
            $storekeeperSignaturePath = null;
            if ($movement->warehouse_signature) {
                $storekeeperSignaturePath = $this->processSignature($movement->warehouse_signature, 'storekeeper_' . $movement->id);
            }

            // Procesar firma del técnico si existe
            $technicianSignaturePath = null;
            if ($movement->technician_signature) {
                $technicianSignaturePath = $this->processSignature($movement->technician_signature, 'technician_' . $movement->id);
            }

            if ($movement->destinationWarehouse) {
                $technian_name = $movement->destinationWarehouse->technician ? $movement->destinationWarehouse->technician->user->name : 'No asignado';
            }

            $data = [
                'title' => 'Constancia de Movimiento',
                'date' => $movement->date,
                'time' => $movement->time,
                'origin' => $movement->warehouse->name,
                'destination' => $movement->destinationWarehouse ? $movement->destinationWarehouse->name : 'No Aplica',
                'movement_type' => $movement->movement->name,
                'folio' => $movement->id,
                'observations' => $movement->description ?? 'Sin observaciones',
                'created_by' => $movement->user->name,
                'storekeeper_signature' => $storekeeperSignaturePath,
                'technician_signature' => $technicianSignaturePath,
                'technician_name' => $technian_name,
                'products' => $movement->products->map(function ($mp) {
                    return [
                        'product' => $mp->product->name,
                        'lot' => $mp->lot->registration_number ?? '-',
                        'amount' => $mp->amount,
                    ];
                })->toArray(),
            ];

            $pdf = Pdf::loadView('stock.movements.show.voucher-pdf', $data);
            return $pdf->stream('movimiento_' . $movement->id . '.pdf');

        } catch (\Exception $e) {
            // Limpiar archivos temporales en caso de error
            $this->cleanTempFiles($movement->id);
            throw $e;
        }
    }

    // Método para procesar imágenes base64
    private function processSignature($base64Image, $filename)
    {
        // Extraer la parte base64 de la cadena
        if (strpos($base64Image, 'base64,') !== false) {
            $base64Image = explode('base64,', $base64Image)[1];
        }

        // Decodificar la imagen base64
        $imageData = base64_decode($base64Image);

        // Crear directorio temporal si no existe
        $tempDir = storage_path('app/temp/signatures/');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Guardar la imagen temporalmente
        $filePath = $tempDir . $filename . '.png';
        file_put_contents($filePath, $imageData);

        return $filePath;
    }

    // Método para limpiar archivos temporales
    private function cleanTempFiles($movementId)
    {
        $tempDir = storage_path('app/temp/signatures/');
        $files = [
            $tempDir . 'storekeeper_' . $movementId . '.png',
            $tempDir . 'technician_' . $movementId . '.png'
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function saveSignatureToTempFile($dataUrl)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'signature_');

        // Extraer los datos base64 del Data URL
        $parts = explode(',', $dataUrl);
        $imageData = base64_decode($parts[1]);

        file_put_contents($tempPath, $imageData);

        return $tempPath;
    }

    public function voucherPreview($id)
    {
        $navigation = $this->navigation;
        $movement = WarehouseMovement::with(['user', 'warehouse', 'destinationWarehouse', 'movementType'])->findOrFail($id);
        $products = MovementProduct::with(['product', 'lot'])->where('movement_id', $id)->get();

        return view('stock.movements.show.voucher-preview', compact('movement', 'products', 'navigation'));
    }
}
