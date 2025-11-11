<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Tracking;
use App\Models\OrderService;
use App\Models\Service;

class TrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $id)
    {
        $orders_data = $dates = [];
        $order = Order::find($id);

        $service_ids = OrderService::where('order_id', $order->id)->pluck('service_id')
            ->unique()
            ->values()
            ->toArray();

        $services = Service::whereIn('id', $service_ids)->select('id', 'name')->get();

        foreach ($service_ids as $service_id) {
            $orders_data[$service_id][] = [
                'id' => $order->id,
                'status' => $order->status_id,
                'date' => $order->programmed_date,
            ];
        }

        $trackings = Tracking::where('order_id', $order->id)->get();
        $tracking = $trackings->first();

        if ($tracking) {
            $range = json_decode($tracking->range);

            foreach ($trackings as $t) {
                $dates[] = [
                    'tracking_id' => $t->id,
                    'date' => $t->next_date,
                    'title' => $t->title,
                    'description' => $t->description,
                    'status' => $t->status
                ];
            }

            $trackings = [];
            $trackings[] = [
                'service_id' => $tracking->service_id,
                'service_name' => $tracking->service->name ?? '',
                'start_date' => $tracking->next_date,
                'frequency' => $range->frequency_type,
                'reps' => $range->frequency,
                'dates' => $dates,
            ];
        }

        $navigation = [
            'Orden de servicio' => route('order.edit', ['id' => $order->id]),
            'Reporte' => route('report.review', ['id' => $order->id]),
            'Seguimientos' => route('tracking.create.order', ['id' => $order->id]),
        ];

        $orders = $orders_data;
        return view('tracking.create', compact('services', 'orders', 'order', 'trackings', 'navigation'));
    }

    public function handle(Request $request)
    {
        $updated_trackings = [];
        $tracking = null;
        $trackings_data = json_decode($request->input('trackings'));
        $order = Order::find($request->order_id);
        $trackable_id = $request->trackable_id;

        foreach ($trackings_data as $tracking) {
            $dates = $tracking->dates;
            foreach ($dates as $d) {
                // Verificar si existe tracking_id en los datos
                $updateData = [
                    'next_date' => $d->date,
                    'range' => json_encode([
                        'frequency' => $tracking->reps,
                        'frequency_type' => $tracking->frequency
                    ]),
                    'title' => $d->title,
                    'description' => $d->description,
                    'status' => $d->status,
                ];

                // Si hay un tracking_id, usarlo como clave para actualización
                if (isset($d->tracking_id) && !empty($d->tracking_id)) {
                    Tracking::updateOrCreate(
                        [
                            'id' => $d->tracking_id, // Buscar por ID existente
                        ],
                        $updateData
                    );
                    $updated_trackings[] = $d->tracking_id;
                } else {
                    // Crear nuevo registro si no hay tracking_id
                    $new_tracking = Tracking::updateOrCreate(
                        [
                            'trackable_id' => $trackable_id,
                            'trackable_type' => $request->trackable_type == 'customer' ? Customer::class : Lead::class,
                            'service_id' => $tracking->service_id,
                            'order_id' => $order->id,
                            'next_date' => $d->date,
                        ],
                        $updateData
                    );
                    $updated_trackings[] = $new_tracking->id;
                }
            }
        }

        Tracking::whereNotIn('id', $updated_trackings)->delete();
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $startOfWeek = now()->startOfMonth();
        $endOfWeek = now()->endOfMonth();

        $update_tracking = json_decode($request->input('tracking'));
        $tracking = Tracking::find($update_tracking->id);

        if ($tracking) {
            $tracking->update([
                'service_id' => $update_tracking->service_id,
                'next_date' => $update_tracking->date,
                'title' => $update_tracking->title,
                'description' => $update_tracking->description,
                'status' => $update_tracking->status
            ]);
        }

        $count_trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
            ->where('status', 'active')
            ->orderBy('next_date')
            ->count();

        return response()->json([
            'count' => $count_trackings,
            'success' => true
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        $startOfWeek = now()->startOfMonth();
        $endOfWeek = now()->endOfMonth();

        $update_tracking = json_decode($request->input('tracking'));
        $tracking = Tracking::find($update_tracking->id);

        if ($tracking) {
            $tracking->update([
                'status' => $update_tracking->status
            ]);
        }

        $count_trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
            ->where('status', 'active')
            ->orderBy('next_date')
            ->count();

        return response()->json([
            'count' => $count_trackings,
            'success' => true
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $startOfWeek = now()->startOfMonth();
        $endOfWeek = now()->endOfMonth();
        
        $update_tracking = json_decode($request->input('tracking'));
        $tracking = Tracking::find($update_tracking->id);

        if ($tracking) {
            $tracking->delete();
        }

        $count_trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
            ->where('status', 'active')
            ->orderBy('next_date')
            ->count();

        return response()->json([
            'count' => $count_trackings,
            'success' => true
        ], 200);
    }
}
