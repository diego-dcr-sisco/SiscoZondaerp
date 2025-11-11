<?php

namespace App\Http\Controllers;

use App\Models\Filenames;
use App\Models\Metric;
use App\Models\ProductInput;
use App\Models\ProductCatalog;
use App\Models\ProductFile;
use App\Models\Biocide;
use App\Models\LineBusiness;
use App\Models\ApplicationMethod;
use App\Models\Purpose;
use App\Models\ToxicityCategories;
use App\Models\Presentation;

use App\Models\PestCategory;
use App\Models\ProductPest;
use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\Customer;
use App\Models\CustomerZone;
use App\Models\Dosage;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;




class ProductController extends Controller
{
    private $images_path = 'products/images/';

    private $files_path = 'products/files/';

    private $size = 50;

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
        //'Estadisticas' => 'stock/analytics',
        // 'Compras' => '/purchase-requisition/purchases',
    ];  

    public function getImage(string $url)
    {
        if (!Storage::disk('public')->exists($url)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($url);
        $type = Storage::disk('public')->mimeType($url);

        return response($file, 200)->header('Content-Type', $type);
    }

    public function index(Request $request): View
    {
        $navigation = $this->navigation;
        $query = ProductCatalog::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('active_ingredient')) {
            $query->where('active_ingredient', 'like', '%' . $request->active_ingredient . '%');
        }

        if ($request->filled('business_name')) {
            $query->where(function ($q) use ($request) {
                $q->where('manufacturer', 'like', '%' . $request->business_name . '%')
                  ->orWhere('supplier_name', 'like', '%' . $request->business_name . '%');
            });
        }

        if ($request->filled('presentation_id')) {
            $query->where('presentation_id', $request->presentation_id);
        }

        // Dirección de ordenamiento
        $direction = strtoupper($request->input('direction', 'DESC'));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        // Tamaño de página
        $size = $request->input('size', $this->size);

        $products = $query->orderBy('id', $direction)->paginate($size);
        $presentations = Presentation::all();
        return view('product.index', compact('products', 'presentations', 'navigation'));
    }

    public function create()
    {
        $navigation = $this->navigation;
        $line_business = LineBusiness::all();
        $application_methods = ApplicationMethod::all();
        $purposes = Purpose::all();
        $biocides = Biocide::all();
        $presentations = Presentation::all();
        $toxics = ToxicityCategories::all();
        $pest_categories = PestCategory::orderBy('category', 'asc')->get();
        $metrics = Metric::all();

        return view(
            'product.create',
            compact('line_business', 'application_methods', 'purposes', 'biocides', 'presentations', 'toxics', 'pest_categories', 'metrics', 'navigation')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $url = null;
        $appMethods_selected = json_decode($request->input('appMethods_selected'), true);
        $pests_selected = json_decode($request->input('pests_selected'), true);
        $product = new ProductCatalog($request->all());

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10000',
            ]);

            $file = $request->file('image');
            $filename = $product->name . '.' . $file->getClientOriginalExtension();
            $url = $this->images_path . $filename;
            Storage::disk('public')->put($url, file_get_contents($file));
            $product->image_path = $url;
        }

        $product->save();

        if (!empty($appMethods_selected)) {
            foreach ($appMethods_selected as $methd_id) {
                Dosage::insert([
                    'prod_id' => $product->id,
                    'methd_id' => $methd_id,
                ]);
            }
        }

        if (!empty($pests_selected)) {
            foreach ($pests_selected as $pest_id) {
                ProductPest::insert([
                    'product_id' => $product->id,
                    'pest_id' => $pest_id,
                ]);
            }
        }

        return redirect()->route('product.index');
    }

    public function storeFile(Request $request, string $id)
    {
        $product = ProductCatalog::find($id);

        if ($product) {
            $filename = Filenames::find($request->input('filename_id'));

            $product_file = new ProductFile();
            $product_file->fill($request->all());
            $product_file->product_id = $product->id;

            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'required|file|mimes:pdf|max:10000',
                ]);

                $file = $request->file('file');
                $dir = $product->name . '_' . $product->id . '/';
                $dir_filename = $filename->name . '.' . $file->getClientOriginalExtension();
                $url = $this->files_path . $dir . $dir_filename;
                Storage::disk('public')->put($url, file_get_contents($file));
                $product_file->path = $url;
            }

            $product_file->save();
        }
        return back();
    }

    public function show(string $id, string $section): View
    {
        $navigation = $this->navigation;
        $product = ProductCatalog::find($id);
        $filenames = Filenames::where('type', 'product')->get();

        return view('product.show', compact('product', 'filenames', 'section', 'navigation'));
    }

    public function edit(string $id)
    {
        $product = ProductCatalog::find($id);
        $line_business = LineBusiness::all();
        $purposes = Purpose::all();
        $biocides = Biocide::all();
        $presentations = Presentation::all();
        $toxics = ToxicityCategories::all();
        $metrics = Metric::all();

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view(
            'product.edit.form',
            compact('product', 'line_business', 'purposes', 'biocides', 'presentations', 'toxics', 'metrics', 'navigation')
        );
    }

    public function editAppMethods(string $id)
    {
        $product = ProductCatalog::find($id);
        $application_methods = ApplicationMethod::orderBy('name')->get();

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view(
            'product.edit.app-methods',
            compact('product', 'application_methods', 'navigation')
        );
    }

    public function editPests(string $id)
    {
        $product = ProductCatalog::find($id);
        $pest_categories = PestCategory::orderBy('category', 'asc')->get();

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view(
            'product.edit.pests',
            compact('product', 'pest_categories', 'navigation')
        );
    }

    public function editFiles(string $id)
    {
        $inputs = [];
        $product = ProductCatalog::find($id);
        $filenames = Filenames::where('type', 'product')->orderBy('name')->get();

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view(
            'product.edit.files',
            compact('product', 'filenames', 'navigation')
        );
    }

    public function editInputs(string $id)
    {
        $inputs = [];
        $product = ProductCatalog::find($id);
        $line_business = LineBusiness::all();
        $application_methods = ApplicationMethod::all();
        $purposes = Purpose::all();
        $biocides = Biocide::all();
        $presentations = Presentation::all();
        $toxics = ToxicityCategories::all();
        $metrics = Metric::all();
        $filenames = Filenames::where('type', 'product')->get();
        $pest_categories = PestCategory::orderBy('category', 'asc')->get();
        $appMethods = ProductInput::where('product_id', $id)->get()->pluck('application_method_id')->unique();

        foreach ($appMethods as $appMethodId) {
            $pestCategories = [];
            $pestCategoryIds = ProductInput::where('product_id', $id)->where('application_method_id', $appMethodId)->get()->pluck('pest_category_id');

            foreach ($pestCategoryIds as $category_id) {
                $pestCategories[] = [
                    'id' => $category_id,
                    'category' => PestCategory::find($category_id)->category,
                    'amount' => ProductInput::where('product_id', $id)->where('application_method_id', $appMethodId)->where('pest_category_id', $category_id)->first()->amount
                ];
            }
            $inputs[] = [
                'application_method_id' => $appMethodId,
                'application_method_name' => ApplicationMethod::find($appMethodId)->name,
                'pestCategories' => $pestCategories,
            ];
        }

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view(
            'product.edit.inputs',
            compact('product', 'line_business', 'application_methods', 'purposes', 'biocides', 'presentations', 'toxics', 'metrics', 'pest_categories', 'inputs', 'filenames', 'navigation')
        );
    }

    public function update(Request $request, string $id)
    {
        $appMethods_selected = json_decode($request->input('appMethods_selected'), true);
        $pests_selected = json_decode($request->input('pests_selected'), true);

        $product = ProductCatalog::find($id);

        if ($product) {
            $product->fill($request->all());

            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000',
                ]);

                $file = $request->file('image');
                $filename = $product->name . '.' . $file->getClientOriginalExtension();
                $url = $this->images_path . $filename;
                Storage::disk('public')->put($url, file_get_contents($file));
                $product->image_path = $url;
            }

            $product->save();


            if (!empty($appMethods_selected)) {
                Dosage::where('prod_id', $product->id)->whereNotIn('methd_id', $appMethods_selected)->delete();
                foreach ($appMethods_selected as $method_id) {
                    Dosage::updateOrCreate([
                        'prod_id' => $product->id,
                        'methd_id' => $method_id
                    ], [
                        'updated_at' => now()
                    ]);
                }
            }

            if ($pests_selected) {
                ProductPest::where('product_id', $product->id)->whereNotIn('pest_id', $pests_selected)->delete();
                foreach ($pests_selected as $pest_id) {
                    ProductPest::updateOrCreate([
                        'product_id' => $product->id,
                        'pest_id' => $pest_id
                    ], [
                        'updated_at' => now()
                    ]);
                }
            }
        }
        return back();
    }

    public function editTreatments(string $id)
    {
        $product = ProductCatalog::find($id);

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];


        return view('product.edit.treatments', compact('navigation'));
    }

    public function editMovements(string $id)
    {
        $product = ProductCatalog::find($id);

        $navigation = [
            'Producto' => route('product.edit', ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas' => route('product.edit.pests', ['id' => $product->id]),
            'Insumos' => route('product.edit.inputs', ['id' => $product->id]),
            'Archivos' => route('product.edit.files', ['id' => $product->id]),
            'Tratamientos' => route('product.edit.treatment', ['id' => $product->id]),
            'Movimientos' => route('product.edit.movements', ['id' => $product->id])
        ];

        return view('product.edit.treatments', compact('navigation'));
    }

    public function input(Request $request, string $id)
    {
        $selected_categories = json_decode($request->input('selected_categories'));
        $appMethod_id = $request->input('application_method_id');
        $pest_categories = ProductInput::where('product_id', $id)->where('application_method_id', $appMethod_id)->get()->pluck('pest_category_id');
        $categoryIds = array_column($selected_categories, 'category_id');
        $delete_categories = array_diff($pest_categories->toArray(), $categoryIds);

        ProductInput::where('product_id', $id)->where('application_method_id', $appMethod_id)->whereIn('pest_category_id', $delete_categories)->delete();

        if (!empty($selected_categories)) {
            foreach ($selected_categories as $pest) {
                ProductInput::updateOrCreate([
                    'product_id' => $id,
                    'application_method_id' => $appMethod_id,
                    'pest_category_id' => $pest->category_id,
                ], [
                    'amount' => $pest->amount,
                ]);
            }
        }
        return back();
    }

    public function search(Request $request)
    {
        $size = $request->input('size');
		$direction = $request->input('direction', 'DESC');
		$query_products = ProductCatalog::query();

		if ($request->name) {
			$query_products = $query_products->where('name', 'LIKE', '%' . $request->name . '%');
		}

		if ($request->business_name) {
			$query_products = $query_products->where('business_name', 'LIKE', '%' . $request->business_name . '%');
		}

		if ($request->active_ingredient) {
			$query_products = $query_products->where('active_ingredient', 'LIKE', '%' . $request->active_ingredient . '%');
		}

		if ($request->presentation_id) {
			$query_products = $query_products->where('presentation_id', $request->presentation_id);
		}


		$products = $query_products->orderBy('name', $direction ?? 'DESC')->paginate($size ?? $this->size)->appends($request->all());
        $presentations = Presentation::all();

		return view(
			'product.index',
			compact(
				'products',
				'presentations',
			)
		);
    }

    public function destroy(string $id)
    {
        $product = ProductCatalog::find($id);
        if ($product) {
            $product->delete();
            return redirect()->route('product.index');
        }
    }

    public function destroyFile(string $id)
    {
        try {
            $product_file = ProductFile::findOrFail($id);

            if ($product_file->path && Storage::disk('public')->exists($product_file->path)) {
                Storage::disk('public')->delete($product_file->path);
            }
            $product_file->delete();
            return back()->with('success', 'Archivo eliminado');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo eliminar el archivo: ' . $e->getMessage());
        }
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////////////  FUNCIONES DE CONSUMOS ////////////////////////////////////

    public function showConsumptions()
    {
        $consumptionsArray = $this->getPastConsumptions(new Request([
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'customer_id' => null
        ]));
        $page = request()->input('page', 1);
        $perPage = 50;
        $consumptions = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($consumptionsArray)->forPage($page, $perPage),
            count($consumptionsArray),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $products = ProductCatalog::all();
        $customers = Customer::select('id', 'name')
            ->orderBy('name')
            ->get();
        $start = null;
        $end = null;
        $currentMonth = now()->locale('es')->translatedFormat('F');
        $customerId = null;

        $isIndex = true;

        return view('stock.consumptions.index', compact(
            'start',
            'end',
            'currentMonth',
            'customerId',
            'customers',
            'products',
            'consumptions',
            'isIndex'
        ));
    }

    public function showFilteredConsumptions(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customer,id'
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $customer = Customer::find($customerId);

        $consumptionsArray = $this->getPastConsumptions(new Request([
            'start_date' => $start,
            'end_date' => $end,
            'customer_id' => $customerId
        ]));
        $page = $request->input('page', 1);
        $perPage = 50;
        $consumptions = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($consumptionsArray)->forPage($page, $perPage),
            count($consumptionsArray),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // dd($consumptions);

        $isIndex = false; // Para mostrar la tabla de cliente especifico 

        return view('stock.consumptions.index', compact(
            'start',
            'end',
            'customerId',
            'customer',
            'consumptions',
            'isIndex'
        ));
    }

    public function getPastConsumptions(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customer,id'
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $customerId = $request->input('customer_id');

        $orders = $customerId
            ? Order::whereBetween('programmed_date', [$start, $end])
                ->where('customer_id', $customerId)
                ->with(['reportProducts'])
                ->get()
            : Order::whereBetween('programmed_date', [$start, $end])
                ->with(['reportProducts'])
                ->get();

        // Crear un array para almacenar los totales por producto
        $productTotals = [];

        foreach ($orders as $order) {
            if ($order->reportProducts->isEmpty()) {
                continue;
            }

            foreach ($order->reportProducts as $product) {
                $orderProduct = OrderProduct::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$orderProduct) {
                    continue;
                }

                $productId = $product->id;

                // Si el producto no existe en el array, inicializarlo
                if (!isset($productTotals[$productId])) {
                    $productTotals[$productId] = [
                        'product' => $product,
                        'amount' => 0,
                        'unit' => $product->metric_id
                    ];
                }

                // Sumar la cantidad al total del producto
                $productTotals[$productId]['amount'] += $orderProduct->amount;
            }
        }

        // Convertir el array asociativo a un array indexado
        $consumptions = array_values($productTotals);

        return $consumptions;
    }

    public function showProductConsumptionDetail($id, Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ], [
            'end_date.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial'
        ]);
        $product = ProductCatalog::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'No se encontró el producto');
        }

        // Obtener fechas del request o usar valores por defecto
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Consulta base de órdenes con el producto
        $orders = OrderProduct::where('product_id', $id)
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('programmed_date', [$startDate, $endDate]);
            })
            ->with(['order.customer', 'service'])
            ->get();

        $details = [];
        foreach ($orders as $order) {
            $details[] = [
                'order_id' => $order->order_id,
                'service' => $order->service->name ?? 'N/A',
                'product_id' => $order->product_id,
                'amount' => $order->amount,
                'unit' => Metric::find($product->metric_id)->type ?? 'N/A',
                'programmed_date' => $order->order->programmed_date ?? 'N/A',
                'customer' => $order->order->customer->name ?? 'N/A',
                'app_method' => ApplicationMethod::find($order->application_method_id)->name ?? 'N/A',
            ];
        }

        $totalConsumption = array_sum(array_column($details, 'amount'));

        // Paginación
        $page = $request->input('page', 1);
        $perPage = 50;
        $items = collect($details);

        $details = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('stock.consumptions.details-by-product', compact(
            'details',
            'product',
            'totalConsumption',
            'startDate',
            'endDate'
        ));
    }

    public function createConsumption()
    {
        $productsCatalog = ProductCatalog::all();
        $customers = Customer::select('id', 'name')
            ->orderBy('name')
            ->get();
        $months = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];
        $zones = CustomerZone::all();
        $currentMonth = now()->locale('es')->translatedFormat('F');

        return view('stock.consumptions.create.index', compact(
            'productsCatalog',
            'months',
            'currentMonth',
            'customers',
            'zones'
        ));
    }

    public function storeConsumption(Request $request)
    {
        dd($request->all());
    }

    public function downloadFile($id)
    {
        try {
            $product_file = ProductFile::find($id);

            if (Storage::disk('public')->exists($product_file->path)) {
                return response()->download(storage_path('app/public/' . $product_file->path));
            }
            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while downloading the file.'], 500);
        }
    }


}
