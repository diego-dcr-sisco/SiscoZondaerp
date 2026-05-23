<?php
namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\ProductRequisition;
use App\Models\Lot;
use App\Models\IndirectProduct;
use App\Models\ProductCatalog;
use App\Models\Supplier;
use App\Models\Customer;

// para generar orden de compra en pdf
use App\PDF\PurchaseOrderPDF;

// para generar excel de las solicitudes de compra
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\AutoFilter;

use Carbon\Carbon;

use Illuminate\Http\Request;

class PurchaseRequisitionController extends Controller
{

    private $status = [
        'Todas',
        'Pendiente',
        'Cotizada',
        'Aprobada',
        'Rechazada',
        'Finalizada'
    ];

    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Zonas' => '/customer-zones',
        'Consumos' => '/consumptions/',
        'Pedidos' => '/consumptions',
        'Productos en ordenes' => '/stock/orders-products',
        //'Estadisticas' => '/stock/analytics',
        'Compras' => '/purchase-requisition/purchases',
    ];

    private $internal_warehouse_name = 'SISCOPLAGAS-MRO';

    public function dashboard()
    {
        $purchaseRequisitions = PurchaseRequisition::all();
        return view('purchase-requisitions.index', compact('purchaseRequisitions'));
    }

    public function index(Request $request)
    {
        $navigation = $this->navigation;
        $states = $this->status;
        $purchaseRequisitions = $this->getFilteredPurchases($request);

        return view('purchase-requisitions.purchases.index', compact('purchaseRequisitions', 'states', 'navigation'));

    }

    public function create()
    {
        $navigation = $this->navigation;
        $user = auth()->user();
        $products = IndirectProduct::where('base_stock', '!=', null)->get();
        $products = $products->sortBy('description');
        $productsCatalog = ProductCatalog::all();
        $productsCatalog = $productsCatalog->sortBy('name');

        $customers = Customer::all();
        $customers = $customers->sortBy('name');

        return view('purchase-requisitions.purchases.create', compact('user', 'products', 'productsCatalog', 'customers', 'navigation'));
    }

    public function store(Request $request)
    {
        // dd($request->all()); // comentado para pruebas
        if (!$request->has('products')) {
            return redirect()->back()->with('error', 'No se recibieron productos en la solicitud.');
        }

        // Convertir los productos mostrados en la tabla a un array de objetos
        $products = json_decode($request->input('products'));
        if (is_null($products)) {
            return redirect()->back()->with('error', 'Los productos enviados no son validos.');
        }

        $purchase = new PurchaseRequisition();
        $purchase->fill($request->all());
        $purchase->user_id = auth()->user()->id;

        // Si el destino es interno, busca el cliente SISCOPLAGAS-MRO y lo asigna a la solicitud
        if ($request->destination_type == 'interno') {
            $customer = Customer::where('name', $this->internal_warehouse_name)->first();
            if ($customer) {
                $purchase->customer_id = $customer->id;
            }
        }

        $lastFolio = PurchaseRequisition::whereYear('created_at', date('Y'))->max('folio');
        $lastFolioNumber = $lastFolio ? intval(substr($lastFolio, -5)) : 0;
        $purchase->folio = 'SC-' . date('Y') . '-' . str_pad($lastFolioNumber + 1, 5, '0', STR_PAD_LEFT);

        $purchase->save();

        if ($products) {
            // estructura de un producto en el array
            // {"index":0,"quantity":"1","unit":"pza","description":"CONTRAC BLOX","type":1}
            foreach ($products as $product) {
                ProductRequisition::insert([
                    'purchase_requisition_id' => $purchase->id,
                    'quantity' => $product->quantity,
                    'unit' => $product->unit,
                    'description' => $product->description,
                    'type' => $product->type,
                    'created_at' => now(),
                ]);

                if ($product->type == 2) {
                    $indirectProduct = IndirectProduct::where('description', $product->description)->first();
                    if (!$indirectProduct) {
                        IndirectProduct::create([
                            'description' => $product->description,
                            'code' => substr(md5($product->description), 0, 8),
                            'created_at' => now(),
                        ]);
                    } else {
                        $indirectProduct->update([
                            'quantity' => $indirectProduct->quantity - $product->quantity,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('purchase-requisition.show', $purchase->id)->with('success', 'Solicitud de compra creada exitosamente.');
    }

    public function show($id)
    {
        $navigation = $this->navigation;
        $requisition = PurchaseRequisition::findOrFail($id);
        $products = ProductRequisition::where('purchase_requisition_id', $id)->get();
        // dd($products);

        // encontrar la cantidad de productos en almacen para cada producto 
        $amounts = $this->getAmounts($products);

        if ($requisition->status == 'Cotizada') {
            $total_1 = $total_2 = 0;

            foreach ($products as $product) {
                $total_1 += $product->quantity * $product->supplier1_cost;
                $total_2 += $product->quantity * $product->supplier2_cost;
            }

            return view('purchase-requisitions.purchases.show', compact('requisition', 'products', 'total_1', 'total_2', 'navigation'));
        }

        return view('purchase-requisitions.purchases.show', compact('requisition', 'products', 'amounts', 'navigation'));
    }

    public function edit($id)
    {
        $navigation = $this->navigation;
        $requisition = PurchaseRequisition::findOrFail($id);
        $products = ProductRequisition::where('purchase_requisition_id', $id)->get();
        $productCatalog = ProductCatalog::all();
        $indirectProducts = IndirectProduct::where('base_stock', '!=', null)->get();
        $customer = Customer::findOrFail($requisition->customer_id);

        return view('purchase-requisitions.purchases.edit.form', compact('requisition', 'products', 'productCatalog', 'customer', 'indirectProducts', 'navigation'));
    }

    public function update(Request $request, $id)
    {
        $navigation = $this->navigation;
        $requisition = PurchaseRequisition::findOrFail($id);

        if (!$request->has('products')) {
            return redirect()->back()->with('error', 'No se recibieron productos en la solicitud.');
        }

        $products = json_decode($request->input('products'), true);
        if (is_null($products)) {
            return redirect()->back()->with('error', 'Los productos enviados no son válidos.');
        }

        // dd($products);

        // Eliminar los productos anteriores
        ProductRequisition::where('purchase_requisition_id', $id)->delete();

        // Insertar los nuevos productos
        foreach ($products as $product) {
            ProductRequisition::create([
                'purchase_requisition_id' => $id,
                'quantity' => $product['quantity'],
                'unit' => $product['unit'],
                'type' => $product['type'],
                'description' => $product['description'],
                'created_at' => now(),
            ]);
        }

        $requisition->observations = $request->observations;
        $requisition->status = 'Pendiente';

        // dd($products);
        $requisition->save();

        return redirect()->route('purchase-requisition.show', $requisition->id)->with('success', 'Solicitud actualizada correctamente')->with('navigation', $navigation);
    }

    public function quote($id)
    {
        $navigation = $this->navigation;
        $requisition = PurchaseRequisition::findOrFail($id);
        $products = ProductRequisition::where('purchase_requisition_id', $id)->get();
        $amounts = $this->getAmounts($products);

        $total_1 = $total_2 = 0;
        foreach ($products as $product) {
            $total_1 += $product->quantity * $product->supplier1_cost;
            $total_2 += $product->quantity * $product->supplier2_cost;
        }
        $suppliers = Supplier::all();
        return view('purchase-requisitions.purchases.quote', compact('requisition', 'products', 'suppliers', 'amounts', 'total_1', 'total_2', 'navigation'  ));
    }

    public function updateQuote(Request $request, int $id)
    {
        $purchase = PurchaseRequisition::findOrFail($id);
        if ($purchase) {
            $products = $purchase->products()->get();
            foreach ($products as $product) {
                $product->supplier1_id = $request->input("supplier1.$product->id");
                $product->supplier1_cost = $request->input("cost1.$product->id");

                $product->supplier2_id = $request->input("supplier2.$product->id");
                $product->supplier2_cost = $request->input("cost2.$product->id");

                $product->save();
            }
            $purchase->status = 'Cotizada';
            $purchase->save();
        }

        return redirect()->route('purchase-requisition.index')->with('success', 'Cotizacion realizada exitosamente.');
    }

    public function approve(Request $request, $id)
    {
        $requisition = PurchaseRequisition::findOrFail($id);

        $approvedSuppliers = $request->input('approved_supplier');

        foreach ($requisition->products as $product) {
            if (isset($approvedSuppliers[$product->id])) {
                $product->approved_supplier_id = $approvedSuppliers[$product->id];
                $product->save();
            }
        }

        $requisition->status = 'Aprobada';
        $requisition->save();

        return redirect()->route('purchase-requisition.show', $id)
            ->with('success', 'Requisición aprobada exitosamente.');
    }

    public function complete(Request $request, $id)
    {
        $requisition = PurchaseRequisition::findOrFail($id);
        $requisition->status = 'Finalizada';
        $requisition->save();
        return redirect()->route('purchase-requisition.show', $id)->with('success', 'Requisicion de compra finalizada');
    }

    public function reject(Request $request, $id)
    {
        $purchase = PurchaseRequisition::findOrFail($id);
        $purchase->observations = $request->rejectionReason;
        $purchase->status = 'Rechazada';
        $purchase->save();
        return redirect()->route('purchase-requisition.index')->with('success', 'Solicitud de compra rechazada exitosamente.');
    }

    public function destroy($id)
    {
        $purchaseRequisition = PurchaseRequisition::findOrFail($id);
        $purchaseRequisition->delete();
        return redirect()->route('purchase-requisition.index')->with('success', 'Solicitud de compra eliminada exitosamente.');
    }

    // Funcion para agregar productos a la solicitud de compra
    public function products(Request $request, PurchaseRequisition $purchase)
    {
        $products = json_decode($request->products, true);
        foreach ($products as $product) {
            $productRequisition = new ProductRequisition();
            $productRequisition->fill($product);
            $productRequisition->purchase_requisition_id = $purchase->id;
            $productRequisition->save();
        }

        return response()->json(['success' => true]);
    }

    // Funcion para obtener la cantidad de stock en almacen de cada producto
    public function getAmounts($products)
    {
        $amount_in_warehouse = [];
        foreach ($products as $product) {
            switch ($product->type) {
                case 'directo':
                    $product_id = ProductCatalog::where('name', $product->description)->first()->id;
                    $lots = Lot::where('product_id', $product_id)->get();
                    $total_warehouse_amount = 0;
                    foreach ($lots as $lot) {
                        $total_warehouse_amount += $lot->ammount;
                    }
                    $amount_in_warehouse[$product->description] = $total_warehouse_amount;
                    break;
                case 'indirecto':
                    $indirectProduct = IndirectProduct::where('description', $product->description)->first();
                    $amount_in_warehouse[$product->description] = $indirectProduct ? $indirectProduct->quantity : 0;
                    break;
                default:
                    $amount_in_warehouse[$product->description] = 0;
                    break;
            }
        }
        return $amount_in_warehouse;
    }

    // Funcion para buscar solicitudes de compra por fecha 
    public function searchByDate(Request $request)
    {
        $purchaseRequisitions = PurchaseRequisition::whereBetween('created_at', [$request->from, $request->to])->get();
        return view('purchase-requisitions.index', compact('purchaseRequisitions'));
    }

    // Funcion para generar la orden de compra en PDF cuando la solicitud esta finalizada
    public function generatePDF($id)
    {
        $purchaseRequisition = PurchaseRequisition::findOrFail($id);
        if ($purchaseRequisition) {
            $pdf_name = 'Orden_Compra' . $purchaseRequisition->folio . '.pdf';
            $pdf = new PurchaseOrderPDF($purchaseRequisition->id);
            $pdf->AddPage();
            $pdf->Products();
            $pdf->Output($pdf_name, 'D');
        }
    }

    public function getFilteredPurchases(Request $request)
    {
        $user = auth()->user();
        $query = PurchaseRequisition::query();

        if ($user->role_id == '1' || $user->work_department_id == 9) {
            $purchaseRequisitions = PurchaseRequisition::paginate(50);
        } else {
            $query->where('user_id', $user->id);
        }

        $date_range = $request->date_range;
        if ($date_range) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date_range));

            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }


        if ($request->has('state') && !empty($request->state) && $request->state !== 'Todas') {
            $query->where('status', $request->state);
        }


        $purchaseRequisitions = $query->orderBy('created_at', 'desc')->paginate(50);
        return $purchaseRequisitions;
    }

    // Funcion para exportar las solicitudes de compra a un archivo Excel
    public function exportExcel(Request $request)
    {
        $purchaseRequisitions = $this->getFilteredPurchases($request);

        // propiedades del archivo Excel
        $properties = new Properties(
            title: 'Solicitudes de compra - ' . Carbon::now()->format('d-m-Y'),
        );
        $options = new Options();
        $options->setProperties($properties);

        // vista del archivo Excel
        $sheetView = new SheetView();
        $sheetView->setZoomScalePageLayoutView(80);

        // Estilo para los encabezados 
        $headerStyle = new Style();
        $headerStyle->setBackgroundColor(Color::BLUE);
        $headerStyle->setFontColor(Color::WHITE);
        $headerStyle->setFontSize(14);
        $headers = ['Folio', 'Dpto. Solicitante', 'Empresa Destino', 'Fecha de creación', 'Estado', 'Productos'];
        $headerRow = Row::fromValues($headers);

        // Estilo para las otras celdas
        $defaultStyle = new Style();
        $defaultStyle->setFontSize(10);
        $defaultStyle->setShouldWrapText(true);

        // Crear el archivo Excel
        $writer = new Writer($options);
        $stateFilter = $request->state ? $request->state : '';
        $filePath = storage_path(
            'app/public/solicitudes_de_compra_' . Carbon::now()->format('d-m-Y').'_'.$stateFilter.'.xlsx'
        );
        $writer->openToFile($filePath);
        $writer->getCurrentSheet()->setSheetView($sheetView);
        $autoFilter = new AutoFilter(0, 1, count($headers) - 1, 1048576);
        $writer->getCurrentSheet()->setAutoFilter($autoFilter);
        $sheet = $writer->getCurrentSheet();
        $sheet->setColumnWidth(15, 1);
        $sheet->setColumnWidth(15,  2);
        $sheet->setColumnWidth(20,  3);
        $sheet->setColumnWidth(15,  4);
        $sheet->setColumnWidth(15,  5);
        $sheet->setColumnWidth(30,  6);

        // Escribir el encabezado
        $headerRow->setStyle($headerStyle);
        $writer->addRow($headerRow);

        // Escribir los datos
        foreach ($purchaseRequisitions as $requisition) {
            $products = $requisition->products->map(function ($product) {
                return $product->description . ' (' . $product->quantity . ' ' . $product->unit . ')';
            })->implode("\n");

            $writer->addRow(Row::fromValues([
                $requisition->folio,
                $requisition->user->workDepartment->name,
                $requisition->customer->name,
                $requisition->created_at->format('d-m-Y'),
                $requisition->status,
                $products
            ]));
        }

        $writer->close();

        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    /////////////////////////////////////////////////////////////////////////
    //////////////////////// FUNCIONES DE CONSUMO DE PRODUCTOS //////////////

    public function indexConsumption() {
        $navigation = $this->navigation;
        return view('purchase-requisitions.consumption.index', compact('navigation'));
    }

}
