<?php

namespace App\Http\Controllers;

use App\Models\ComercialZone;
use App\Models\ComercialZoneCustomer;
use App\Models\Customer;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ComercialZoneController extends Controller
{
    private $size = 50; // Paginación

    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Zonas comerciales' => '/comercial-zones',
        'Consumos' => [
            'Nuevos' => '/consumptions',
            'Por cliente' => '/stock/consumptions/by-customer',
            'En ordenes' => '/stock/movements/orders',
        ],
        'Productos en ordenes' => '/stock/orders-products',
        //'Estadisticas' => 'stock/analytics',
        'Compras' => '/purchase-requisition/purchases',
    ];


    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $size = (int) $request->input('size', $this->size);
        $size = in_array($size, [25, 50, 100, 200], true) ? $size : $this->size;

        $query = ComercialZone::with('customers')
            ->withCount('customers');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhereHas('customers', function ($customerQuery) use ($search) {
                        $customerQuery->where('customer.name', 'like', '%' . $search . '%')
                            ->orWhere('customer.code', 'like', '%' . $search . '%');
                    });
            });
        }

        $comercial_zones = $query
            ->orderBy('name')
            ->paginate($size)
            ->appends($request->query());

        $summaryQuery = ComercialZone::withCount('customers');
        $zonesSummary = [
            'total' => (clone $summaryQuery)->count(),
            'without_customers' => ComercialZone::doesntHave('customers')->count(),
            'filtered' => $comercial_zones->total(),
            'unique_customers' => ComercialZoneCustomer::distinct('customer_id')->count('customer_id'),
        ];
        $navigation = $this->navigation;

        return view('comercial_zones.index', compact(
            'comercial_zones',
            'zonesSummary',
            'search',
            'size',
            'navigation'
        ));
    }

    public function create()
    {
        $navigation = $this->navigation;
        return view('comercial_zones.create', compact('navigation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'customer_ids' => ['required', 'string'],
        ]);

        $customer_ids = $this->parseCustomerIds($request->input('customer_ids'));

        if (empty($customer_ids)) {
            return back()->withErrors([
                'customer_ids' => 'Seleccione al menos un cliente.',
            ])->withInput();
        }

        $count_zones = ComercialZone::count();

        $comercial_zone = new ComercialZone();
        $comercial_zone->code = 'ZN-' . ($count_zones + 1);
        $comercial_zone->fill($request->all());
        $comercial_zone->save();

        $comercial_zone->customers()->sync($customer_ids);

        return redirect()->route('comercial-zones.index')
            ->with('success', 'Zona comercial creada exitosamente.');
    }

    public function show(string $id)
    {
        $zone = ComercialZone::with('customers')->findOrFail($id);
        $navigation = $this->navigation;
        return view('stock.consumptions.customer-zones.show', compact('zone', 'navigation'));
    }

    public function edit(string $id)
    {
        $comercialZone = ComercialZone::with('customers')->findOrFail($id);
        $navigation = $this->navigation;

        return view('comercial_zones.edit', compact('comercialZone', 'navigation'));
    }

    public function update(Request $request, string $id)
    {
        $zone = ComercialZone::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('comercial_zones', 'code')->ignore($zone->id)],
            'description' => ['nullable', 'string'],
            'customer_ids' => ['required', 'string'],
        ], [
            'name.required' => 'El nombre de la zona comercial es obligatorio.',
            'customer_ids.required' => 'Debe seleccionar al menos un cliente.',
        ]);

        $customer_ids = $this->parseCustomerIds($request->input('customer_ids'));

        if (empty($customer_ids)) {
            return $request->ajax()
                ? response()->json(['errors' => ['customer_ids' => ['Seleccione al menos un cliente.']]], 422)
                : back()->withErrors(['customer_ids' => 'Seleccione al menos un cliente.'])->withInput();
        }

        $zone->update([
            'name' => $request->input('name'),
            'code' => $request->input('code') ?: $zone->code,
            'description' => $request->input('description'),
        ]);

        $zone->customers()->sync($customer_ids);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('comercial-zones.index')
            ->with('success', 'Zona actualizada exitosamente', 'navigation');
    }

    public function destroy(string $id)
    {
        $zone = ComercialZone::findOrFail($id);
        $zone->customers()->detach();

        $zone->delete();

        return redirect()->route('comercial-zones.index')
            ->with('success', 'Zona eliminada exitosamente', 'navigation');
    }

    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $customerId = $request->get('customer_id');

        $query = ComercialZone::where('name', 'LIKE', '%' . $term . '%');

        if ($customerId) {
            $query->whereHas('customers', function ($q) use ($customerId) {
                $q->where('customer.id', $customerId);
            });
        }

        $zones = $query->with('customers')->limit(10)->get();

        return response()->json([
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'code' => $zone->code,
                    'customers' => $zone->customers->pluck('name')->values(),
                ];
            })
        ]);
    }

    public function getZonesByCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer,id'
        ]);

        $zones = ComercialZone::whereHas('customers', function ($q) use ($request) {
                $q->where('customer.id', $request->customer_id);
            })
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'zones' => $zones
        ]);
    }

    private function parseCustomerIds(?string $customerIds): array
    {
        if (!$customerIds) {
            return [];
        }

        $decoded = json_decode($customerIds, true);

        if (!is_array($decoded)) {
            $decoded = explode(',', $customerIds);
        }

        return collect($decoded)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
