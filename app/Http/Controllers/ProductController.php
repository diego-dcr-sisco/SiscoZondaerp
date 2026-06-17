<?php

namespace App\Http\Controllers;

use App\Models\PestCategory;
use App\Models\PestCatalog;
use App\Models\ProductPest;
use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\Customer;
use App\Models\CustomerZone;
use App\Models\Dosage;
use App\Models\ProductInput;
use App\Models\ProductCatalog;
use App\Models\Presentation;
use App\Models\ApplicationMethod;
use App\Models\Purpose;
use App\Models\Biocide;
use App\Models\LineBusiness;
use App\Models\ToxicityCategories;
use App\Models\ProductFile;
use App\Models\Filenames;
use App\Models\Metric;
use App\Models\ProductTreatment;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private string $images_path = 'products/images/';
    private string $files_path = 'products/files/';
    private int $size = 50;
    public array $navigation = [];

    public function __construct()
    {
        $this->navigation = config('stock_navigation.items');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function productEditNavigation(ProductCatalog $product): array
    {
        return [
            'Producto'              => route('product.edit',            ['id' => $product->id]),
            'Métodos de aplicación' => route('product.edit.appMethods', ['id' => $product->id]),
            'Plagas'                => route('product.edit.pests',       ['id' => $product->id]),
            'Insumos'               => route('product.edit.inputs',      ['id' => $product->id]),
            'Archivos'              => route('product.edit.files',       ['id' => $product->id]),
            'Tratamientos'          => route('product.edit.treatment',   ['id' => $product->id]),
            'Movimientos'           => route('product.edit.movements',   ['id' => $product->id]),
        ];
    }

    private function getCleanMetric(?string $rawMetric): string
    {
        $cleanKey = strtolower(trim(preg_replace('/[\(\)]/', '', $rawMetric ?? '')));

        $map = [
            'units'      => 'uds',
            'unit'       => 'uds',
            'uds'        => 'uds',
            'wt'         => 'g',
            'weight'     => 'g',
            'grams'      => 'g',
            'gramos'     => 'g',
            'g'          => 'g',
            'vol'        => 'ml',
            'volume'     => 'ml',
            'mililiters' => 'ml',
            'mililitros' => 'ml',
            'ml'         => 'ml',
            'l'          => 'l',
            'kg'         => 'kg',
            'gts'        => 'gts',
        ];

        return $map[$cleanKey] ?? $rawMetric ?? 'uds';
    }

    // -------------------------------------------------------------------------
    // Images / Files
    // -------------------------------------------------------------------------

    public function getImage(string $url)
    {
        if (!Storage::disk('public')->exists($url)) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $file = $disk->get($url);
        $type = $disk->mimeType($url);

        return response($file, 200)->header('Content-Type', $type);
    }

    public function downloadFile(string $id)
    {
        try {
            $productFile = ProductFile::findOrFail($id);

            if (Storage::disk('public')->exists($productFile->path)) {
                return response()->download(storage_path('app/public/' . $productFile->path));
            }

            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while downloading the file.'], 500);
        }
    }

    public function storeFile(Request $request, string $id)
    {
        $product = ProductCatalog::find($id);

        if (!$product) {
            return back()->with('error', 'Producto no encontrado.');
        }

        $filename = Filenames::find($request->input('filename_id'));

        $productFile = new ProductFile();
        $productFile->fill($request->all());
        $productFile->product_id = $product->id;

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:pdf,xlsx|max:10000',
            ]);

            $file        = $request->file('file');
            $dir         = $product->name . '_' . $product->id . '/';
            $dirFilename = $filename->name . '.' . $file->getClientOriginalExtension();
            $url         = $this->files_path . $dir . $dirFilename;

            Storage::disk('public')->put($url, file_get_contents($file));
            $productFile->path = $url;
        }

        $productFile->save();

        return back();
    }

    public function destroyFile(string $id)
    {
        try {
            $productFile = ProductFile::findOrFail($id);

            if ($productFile->path && Storage::disk('public')->exists($productFile->path)) {
                Storage::disk('public')->delete($productFile->path);
            }

            $productFile->delete();

            return back()->with('success', 'Archivo eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el archivo: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Product CRUD
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $navigation = $this->navigation;
        $query      = ProductCatalog::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('active_ingredient')) {
            $query->where('active_ingredient', 'like', '%' . $request->active_ingredient . '%');
        }

        if ($request->filled('business_name')) {
            $query->where(function ($q) use ($request) {
                $q->where('manufacturer',    'like', '%' . $request->business_name . '%')
                    ->orWhere('supplier_name', 'like', '%' . $request->business_name . '%');
            });
        }

        if ($request->filled('presentation_id')) {
            $query->where('presentation_id', $request->presentation_id);
        }

        $direction = strtoupper($request->input('direction', 'DESC'));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        $size = $request->input('size', $this->size);

        $products = $query
            ->with(['presentation:id,name', 'metric:id,value'])
            ->orderBy('id', $direction)
            ->paginate($size)
            ->withQueryString();

        $presentations = Cache::remember('catalog.presentations.all', now()->addHour(), function () {
            return Presentation::orderBy('name')->get(['id', 'name']);
        });

        return view('product.index', compact('products', 'presentations', 'navigation'));
    }

    public function create()
    {
        $navigation         = $this->navigation;
        $line_business      = LineBusiness::all();
        $application_methods = ApplicationMethod::all();
        $purposes           = Purpose::all();
        $biocides           = Biocide::all();
        $presentations      = Presentation::all();
        $toxics             = ToxicityCategories::all();
        $pest_categories    = PestCategory::orderBy('category')->get();
        $metrics            = Metric::all();

        return view('product.create', compact(
            'line_business',
            'application_methods',
            'purposes',
            'biocides',
            'presentations',
            'toxics',
            'pest_categories',
            'metrics',
            'navigation'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $appMethods_selected = json_decode($request->input('appMethods_selected'), true);
        $pests_selected      = json_decode($request->input('pests_selected'), true);

        $product = new ProductCatalog($request->all());

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10000',
            ]);

            $file     = $request->file('image');
            $filename = $product->name . '.' . $file->getClientOriginalExtension();
            $url      = $this->images_path . $filename;

            Storage::disk('public')->put($url, file_get_contents($file));
            $product->image_path = $url;
        }

        $product->save();

        if (!empty($appMethods_selected)) {
            foreach ($appMethods_selected as $methd_id) {
                Dosage::insert(['prod_id' => $product->id, 'methd_id' => $methd_id]);
            }
        }

        if (!empty($pests_selected)) {
            foreach ($pests_selected as $pest_id) {
                ProductPest::insert(['product_id' => $product->id, 'pest_id' => $pest_id]);
            }
        }

        return redirect()->route('product.index');
    }

    public function show(string $id, string $section): View
    {
        $navigation = $this->navigation;
        $product    = ProductCatalog::find($id);
        $filenames  = Filenames::where('type', 'product')->get();

        return view('product.show', compact('product', 'filenames', 'section', 'navigation'));
    }

    public function destroy(string $id)
    {
        $product = ProductCatalog::find($id);

        if ($product) {
            $product->delete();
            return redirect()->route('product.index');
        }
    }

    public function search(Request $request)
    {
        $size      = $request->input('size', $this->size);
        $direction = strtoupper($request->input('direction', 'DESC'));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        $query = ProductCatalog::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('business_name')) {
            $query->where('business_name', 'like', '%' . $request->business_name . '%');
        }

        if ($request->filled('active_ingredient')) {
            $query->where('active_ingredient', 'like', '%' . $request->active_ingredient . '%');
        }

        if ($request->filled('presentation_id')) {
            $query->where('presentation_id', $request->presentation_id);
        }

        $products = $query
            ->with(['presentation:id,name', 'metric:id,value'])
            ->orderBy('name', $direction)
            ->paginate($size)
            ->appends($request->all());

        $presentations = Cache::remember('catalog.presentations.all', now()->addHour(), function () {
            return Presentation::orderBy('name')->get(['id', 'name']);
        });

        return view('product.index', compact('products', 'presentations'));
    }

    // -------------------------------------------------------------------------
    // Edit sections
    // -------------------------------------------------------------------------

    public function edit(string $id)
    {
        $product       = ProductCatalog::findOrFail($id);
        $line_business = LineBusiness::all();
        $purposes      = Purpose::all();
        $biocides      = Biocide::all();
        $presentations = Presentation::all();
        $toxics        = ToxicityCategories::all();
        $metrics       = Metric::all();

        $navigation      = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.form', compact(
            'product',
            'line_business',
            'purposes',
            'biocides',
            'presentations',
            'toxics',
            'metrics',
            'navigation',
            'productNavigation'
        ));
    }

    public function editAppMethods(string $id)
    {
        $product             = ProductCatalog::findOrFail($id);
        $application_methods = ApplicationMethod::orderBy('name')->get();

        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.app-methods', compact(
            'product',
            'application_methods',
            'navigation',
            'productNavigation'
        ));
    }

    public function editPests(string $id)
    {
        $product         = ProductCatalog::findOrFail($id);
        $pest_categories = PestCategory::orderBy('category')->get();

        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.pests', compact(
            'product',
            'pest_categories',
            'navigation',
            'productNavigation'
        ));
    }

    public function editFiles(string $id)
    {
        $product   = ProductCatalog::findOrFail($id);
        $filenames = Filenames::where('type', 'product')->orderBy('name')->get();

        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.files', compact(
            'product',
            'filenames',
            'navigation',
            'productNavigation'
        ));
    }

    public function editInputs(string $id)
    {
        $product     = ProductCatalog::with('metric')->findOrFail($id);
        $metricValue = $product->metric->value ?? 'uds';

        if (!empty($product->metric->type)) {
            $shortMetric = $product->metric->type;
        } elseif (preg_match('/\(([^)]+)\)/', $metricValue, $matches)) {
            $shortMetric = $matches[1];
        } else {
            $shortMetric = $metricValue;
        }

        $line_business       = LineBusiness::all();
        $application_methods = ApplicationMethod::all();
        $purposes            = Purpose::all();
        $biocides            = Biocide::all();
        $presentations       = Presentation::all();
        $toxics              = ToxicityCategories::all();
        $metrics             = Metric::all();
        $filenames           = Filenames::where('type', 'product')->get();
        $pest_categories     = PestCategory::orderBy('category')->get();

        $inputsByMethod = ProductInput::where('product_id', $id)
            ->orderBy('application_method_id')
            ->get()
            ->groupBy('application_method_id');

        $inputs = [];
        foreach ($inputsByMethod as $appMethodId => $inputsGroup) {
            $pestCategories = [];

            foreach ($inputsGroup as $input) {
                $raw = $input->metric ?? $product->metric->value ?? 'uds';

                $pestCategories[] = [
                    'id'             => $input->pest_category_id,
                    'category'       => PestCategory::find($input->pest_category_id)?->category ?? 'Sin categoría',
                    'amount'         => $input->amount,
                    'display_metric' => $this->getCleanMetric($raw),
                    'pest_ids'       => is_array($input->pest_ids)
                        ? $input->pest_ids
                        : (json_decode($input->pest_ids, true) ?? []),
                ];
            }

            $inputs[] = [
                'application_method_id'   => intval($appMethodId),
                'application_method_name' => ApplicationMethod::find($appMethodId)?->name ?? 'Método desconocido',
                'pestCategories'          => $pestCategories,
            ];
        }

        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.inputs', compact(
            'product',
            'line_business',
            'application_methods',
            'purposes',
            'biocides',
            'presentations',
            'toxics',
            'metrics',
            'pest_categories',
            'inputs',
            'filenames',
            'navigation',
            'shortMetric',
            'productNavigation'
        ));
    }

    public function updateInputs(Request $request, string $id)
    {
        $request->validate([
            'application_method_id' => 'required|integer',
            'selected_categories'   => 'required',
        ]);

        $appMethodId         = $request->input('application_method_id');
        $selectedCategoriesRaw = $request->input('selected_categories', '[]');
        $selectedCategories  = is_array($selectedCategoriesRaw)
            ? $selectedCategoriesRaw
            : json_decode($selectedCategoriesRaw, true);

        if (!is_array($selectedCategories)) {
            $selectedCategories = [];
        }

        try {
            ProductInput::where('product_id', $id)
                ->where('application_method_id', $appMethodId)
                ->delete();

            foreach ($selectedCategories as $item) {
                $categoryId = isset($item['id']) ? intval($item['id']) : null;

                if (empty($categoryId)) {
                    continue;
                }

                $rawAmount = $item['amount'] ?? 0;
                $amount    = is_string($rawAmount)
                    ? floatval(str_replace(',', '.', $rawAmount))
                    : floatval($rawAmount);

                $pestIdsRaw = $item['pest_ids'] ?? [];
                if (is_string($pestIdsRaw)) {
                    $decoded    = json_decode($pestIdsRaw, true);
                    $pestIdsRaw = is_array($decoded) ? $decoded : [];
                }

                if (!is_array($pestIdsRaw)) {
                    $pestIdsRaw = [];
                }

                $pestIds = array_values(array_filter(
                    array_map('intval', $pestIdsRaw),
                    fn($v) => $v > 0
                ));

                ProductInput::updateOrCreate(
                    [
                        'product_id'            => $id,
                        'application_method_id' => $appMethodId,
                        'pest_category_id'      => $categoryId,
                    ],
                    [
                        'amount'   => $amount,
                        'pest_ids' => $pestIds,
                    ]
                );
            }

            return back()->with('success', 'Configuración de insumos actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar insumos del producto {$id}: " . $e->getMessage());

            return back()->with('error', 'Ocurrió un error interno al guardar la configuración.');
        }
    }

    public function editTreatments(string $id)
    {
        $product           = ProductCatalog::with('treatments')->findOrFail($id);
        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.treatments', compact('product', 'navigation', 'productNavigation'));
    }

    public function input(Request $request, string $id)
    {
        $selected_categories = json_decode($request->input('selected_categories'), true);
        $appMethod_id        = $request->input('application_method_id');

        if (!is_array($selected_categories)) {
            $selected_categories = [];
        }

        $pest_categories = ProductInput::where('product_id', $id)
            ->where('application_method_id', $appMethod_id)
            ->get()
            ->pluck('pest_category_id');

        $categoryIds       = array_column($selected_categories, 'id');
        $delete_categories = array_diff($pest_categories->toArray(), $categoryIds);

        ProductInput::where('product_id', $id)
            ->where('application_method_id', $appMethod_id)
            ->whereIn('pest_category_id', $delete_categories)
            ->delete();

        if (!empty($selected_categories)) {
            foreach ($selected_categories as $pest) {
                $categoryId = isset($pest['id']) ? intval($pest['id']) : null;

                if (empty($categoryId)) {
                    continue;
                }

                $rawAmount = $pest['amount'] ?? 0;
                $amount    = is_string($rawAmount)
                    ? floatval(str_replace(',', '.', $rawAmount))
                    : floatval($rawAmount);

                $pestIdsRaw = $pest['pest_ids'] ?? [];
                if (is_string($pestIdsRaw)) {
                    $decoded    = json_decode($pestIdsRaw, true);
                    $pestIdsRaw = is_array($decoded) ? $decoded : [];
                }
                $pestIds = is_array($pestIdsRaw) ? array_values(array_filter(array_map('intval', $pestIdsRaw))) : [];

                ProductInput::updateOrCreate(
                    [
                        'product_id'            => $id,
                        'application_method_id' => $appMethod_id,
                        'pest_category_id'      => $categoryId,
                    ],
                    [
                        'amount'   => $amount,
                        'pest_ids' => $pestIds, // Se guarda mapeado correctamente
                    ]
                );
            }

            return back()->with('success', 'Configuración de insumos actualizada correctamente.');
        }

        return back()->with('error', 'Error al procesar la solicitud.');
    }

    public function storeTreatment(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        ProductTreatment::create([
            'product_id'  => $id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? 0.00,
        ]);

        return back()->with('success', 'Tratamiento registrado correctamente.');
    }

    public function updateTreatment(Request $request, string $id, string $treatmentId)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $treatment = ProductTreatment::where('product_id', $id)->findOrFail($treatmentId);
        $treatment->update($validated);

        return back()->with('success', 'Tratamiento actualizado correctamente.');
    }

    public function destroyTreatment(string $id, string $treatmentId)
    {
        $treatment = ProductTreatment::where('product_id', $id)->findOrFail($treatmentId);
        $treatment->delete();

        return back()->with('success', 'Tratamiento eliminado correctamente.');
    }

    // -------------------------------------------------------------------------
    // Movements
    // -------------------------------------------------------------------------

    public function editMovements(Request $request, string $id)
    {
        $product = ProductCatalog::findOrFail($id);

        $query = \App\Models\MovementProduct::where('product_id', $id)
            ->with(['warehouseMovement', 'movement', 'warehouse', 'lot', 'product']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ]);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('lot_id')) {
            $query->where('lot_id', $request->lot_id);
        }

        if ($request->filled('type')) {
            $typeSelected = strtolower($request->type);
            $query->where(function ($q) use ($typeSelected) {
                $q->whereHas('movement', function ($sub) use ($typeSelected) {
                    $sub->where('type', 'like', '%' . $typeSelected . '%')
                        ->orWhere('name', 'like', '%' . $typeSelected . '%');
                })->orWhereHas('warehouseMovement', function ($sub) use ($typeSelected) {
                    $sub->where('type', 'like', '%' . $typeSelected . '%');
                });
            });
        }

        $allFilteredMovements = (clone $query)->get();
        $totalEntries = 0;
        $totalExits   = 0;

        foreach ($allFilteredMovements as $mv) {
            $movementType = strtolower(
                $mv->type
                    ?? $mv->movement->type
                    ?? $mv->movement->name
                    ?? $mv->warehouseMovement->type
                    ?? ''
            );

            if (
                str_contains($movementType, 'ingreso')  ||
                str_contains($movementType, 'entrada')  ||
                str_contains($movementType, 'compra')
            ) {
                $totalEntries += $mv->quantity ?? 0;
            } else {
                $totalExits += $mv->quantity ?? 0;
            }
        }

        $netBalance = $totalEntries - $totalExits;

        $movements = $query->orderBy('created_at', 'DESC')
            ->paginate(15)
            ->withQueryString();

        $warehouses = \App\Models\Warehouse::orderBy('name')->get(['id', 'name']);
        $lots       = \App\Models\Lot::where('product_id', $id)->get();

        $navigation        = $this->navigation;
        $productNavigation = $this->productEditNavigation($product);

        return view('product.edit.movements', compact(
            'product',
            'movements',
            'navigation',
            'productNavigation',
            'totalEntries',
            'totalExits',
            'netBalance',
            'warehouses',
            'lots'
        ));
    }

    // -------------------------------------------------------------------------
    // Consumptions
    // -------------------------------------------------------------------------

    public function showConsumptions()
    {
        $consumptionsArray = $this->getPastConsumptions(new Request([
            'start_date'  => now()->subMonth()->toDateString(),
            'end_date'    => now()->toDateString(),
            'customer_id' => null,
        ]));

        $page    = request()->input('page', 1);
        $perPage = 50;

        $consumptions = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($consumptionsArray)->forPage($page, $perPage),
            count($consumptionsArray),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $products     = ProductCatalog::all();
        $customers    = Customer::select('id', 'name')->orderBy('name')->get();
        $currentMonth = now()->locale('es')->translatedFormat('F');
        $start        = null;
        $end          = null;
        $customerId   = null;
        $isIndex      = true;

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
        $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customer,id',
        ]);

        $start      = $request->input('start_date');
        $end        = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $customer   = Customer::find($customerId);

        $consumptionsArray = $this->getPastConsumptions(new Request([
            'start_date'  => $start,
            'end_date'    => $end,
            'customer_id' => $customerId,
        ]));

        $page    = $request->input('page', 1);
        $perPage = 50;

        $consumptions = new \Illuminate\Pagination\LengthAwarePaginator(
            collect($consumptionsArray)->forPage($page, $perPage),
            count($consumptionsArray),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $isIndex = false;

        return view('stock.consumptions.index', compact(
            'start',
            'end',
            'customerId',
            'customer',
            'consumptions',
            'isIndex'
        ));
    }

    public function getPastConsumptions(Request $request): array
    {
        $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'customer_id' => 'nullable|exists:customer,id',
        ]);

        $start      = $request->input('start_date');
        $end        = $request->input('end_date');
        $customerId = $request->input('customer_id');

        $ordersQuery = Order::whereBetween('programmed_date', [$start, $end])
            ->with(['reportProducts']);

        if ($customerId) {
            $ordersQuery->where('customer_id', $customerId);
        }

        $orders        = $ordersQuery->get();
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

                if (!isset($productTotals[$productId])) {
                    $productTotals[$productId] = [
                        'product' => $product,
                        'amount'  => 0,
                        'uds'     => $product->metric_id,
                    ];
                }

                $productTotals[$productId]['amount'] += $orderProduct->amount;
            }
        }

        return array_values($productTotals);
    }

    public function showProductConsumptionDetail(string $id, Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
        ]);

        $product = ProductCatalog::find($id);

        if (!$product) {
            return back()->with('error', 'No se encontró el producto.');
        }

        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));

        $orderProducts = OrderProduct::where('product_id', $id)
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('programmed_date', [$startDate, $endDate]);
            })
            ->with(['order.customer', 'service'])
            ->get();

        // FIX: each iteration must append to the array, not overwrite it
        $details = [];
        foreach ($orderProducts as $orderProduct) {
            $details[] = [
                'order_id'       => $orderProduct->order_id,
                'service'        => $orderProduct->service->name ?? 'N/A',
                'product_id'     => $orderProduct->product_id,
                'amount'         => $orderProduct->amount,
                'uds'            => Metric::find($product->metric_id)->type ?? 'N/A',
                'programmed_date' => $orderProduct->order->programmed_date ?? 'N/A',
                'customer'       => $orderProduct->order->customer->name ?? 'N/A',
                'app_method'     => ApplicationMethod::find($orderProduct->application_method_id)->name ?? 'N/A',
            ];
        }

        $totalConsumption = array_sum(array_column($details, 'amount'));

        $page    = $request->input('page', 1);
        $perPage = 50;
        $items   = collect($details);

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
        $customers       = Customer::select('id', 'name')->orderBy('name')->get();
        $zones           = CustomerZone::all();
        $currentMonth    = now()->locale('es')->translatedFormat('F');

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
            'Diciembre',
        ];

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

    // -------------------------------------------------------------------------
    // API / JSON endpoints
    // -------------------------------------------------------------------------

    public function getPestsByCategory(string $categoryId)
    {
        $pests = PestCatalog::where('pest_category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name', 'pest_category_id']);

        return response()->json($pests);
    }
}
