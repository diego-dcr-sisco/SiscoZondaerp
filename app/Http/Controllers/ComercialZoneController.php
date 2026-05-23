<?php

namespace App\Http\Controllers;

use App\Models\ComercialZone;
use App\Models\ComercialZoneCustomer;
use App\Models\Customer;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ComercialZoneController extends Controller
{
    private $size = 50; // Paginación

    public $navigation = [
        'Almacenes' => '/stock',
        'Lotes' => '/lot/index',
        'Productos' => '/products',
        'Movimientos' => '/stock/movements',
        'Zonas' => '/customer-zones',
        'Consumos' => '/consumptions/',
        'Pedidos' => '/consumptions',
        'Productos en ordenes' => '/stock/orders-products',
        //'Estadisticas' => 'stock/analytics',
        'Compras' => '/purchase-requisition/purchases',
    ];


    public function index(Request $request)
    {
        $comercial_zones = ComercialZone::orderBy('name')->paginate($this->size);

        return view('comercial_zones.index', compact(
            'comercial_zones'
        ));
    }

    public function create()
    {
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $zones = Zone::select('id', 'name')->orderBy('name')->get();
        $navigation = $this->navigation;
        return view('stock.consumptions.customer-zones.create', compact('customers', 'zones', 'navigation'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $customer_ids = json_decode($request->input('customer_ids'), true);

        $count_zones = ComercialZone::count();

        $comercial_zone = new ComercialZone();
        $comercial_zone->code = 'ZN-' . ($count_zones + 1);
        $comercial_zone->fill($request->all());
        $comercial_zone->save();

        foreach ($customer_ids as $customer_id) {
            ComercialZoneCustomer::create([
                'comercial_zone_id' => $comercial_zone->id,
                'customer_id' => $customer_id
            ]);
        }

        return back();
    }

    public function show(string $id)
    {
        $zone = ComercialZone::with(['customer', 'consumptions.product'])->findOrFail($id);
        $navigation = $this->navigation;
        return view('stock.consumptions.customer-zones.show', compact('zone', 'navigation'));
    }

    public function edit(string $id)
    {
        $ComercialZone = ComercialZone::with('customer')->findOrFail($id);
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $zones = Zone::select('id', 'name')->orderBy('name')->get();
        $navigation = $this->navigation;
        return view('stock.consumptions.customer-zones.edit', compact('ComercialZone', 'customers', 'zones', 'navigation'));
    }

    public function update(Request $request, string $id)
    {
        $zone = ComercialZone::findOrFail($id);

        $request->validate([
            'customer_id' => 'required|exists:customer,id',
            'zone_id' => 'required|exists:zones,id',
            'status' => 'nullable|string|in:active,inactive',
            'observation' => 'nullable|string|max:500',
        ], [
            'customer_id.required' => 'Debe seleccionar un cliente',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'zone_id.required' => 'Debe seleccionar una zona',
            'zone_id.exists' => 'La zona seleccionada no existe',
        ]);

        $zone->update(
            [
                'customer_id' => $request->customer_id,
                'zone_id' => $request->zone_id,
                'status' => $request->status,
                'observation' => $request->observation,
            ]
        );

        return redirect()->route('comercial-zones.index')
            ->with('success', 'Zona actualizada exitosamente', 'navigation');
    }

    public function destroy(string $id)
    {
        $zone = ComercialZone::findOrFail($id);

        // Verificar si tiene consumos asociados
        if ($zone->consumptions()->count() > 0) {
            return redirect()->route('comercial-zones.index')
                ->with('error', 'No se puede eliminar la zona porque tiene consumos asociados');
        }

        // Eliminar archivo si existe
        if ($zone->file && Storage::disk('public')->exists($zone->file)) {
            Storage::disk('public')->delete($zone->file);
        }

        $zone->delete();

        return redirect()->route('comercial-zones.index')
            ->with('success', 'Zona eliminada exitosamente', 'navigation');
    }

    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $customerId = $request->get('customer_id');

        $query = ComercialZone::where('zone', 'LIKE', '%' . $term . '%');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $zones = $query->with('customer')->limit(10)->get();

        return response()->json([
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'zone' => $zone->zone,
                    'customer' => $zone->customer->name,
                    'status' => $zone->status_formatted
                ];
            })
        ]);
    }

    public function getZonesByCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer,id'
        ]);

        $zones = ComercialZone::where('customer_id', $request->customer_id)
            ->where('status', 'active')
            ->select('id', 'zone')
            ->orderBy('zone')
            ->get();

        return response()->json([
            'success' => true,
            'zones' => $zones
        ]);
    }
}