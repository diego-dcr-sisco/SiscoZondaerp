<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\Customer;
use App\Models\Service;

class ScheduleController extends Controller
{
    public $hrs_format = [
        "00:00:00", "01:00:00", "02:00:00", "03:00:00", "04:00:00", "05:00:00",
        "06:00:00", "07:00:00", "08:00:00", "09:00:00", "10:00:00", "11:00:00",
        "12:00:00", "13:00:00", "14:00:00", "15:00:00", "16:00:00", "17:00:00",
        "18:00:00", "19:00:00", "20:00:00", "21:00:00", "22:00:00", "23:00:00"
    ];
      
    public function index()
    {
        $daily_program = [];
        $customers = Customer::all();
        $order_services = OrderService::all();
        $services = Service::all();
        $status = OrderStatus::all();
        $date = Carbon::today()->format('d-m-Y');
        $orders = Order::where('programmed_date', Carbon::today()->format('Y-m-d'))->orderBy('start_time')->get();
        $hrs_array = $this->hrs_format;

        if($orders) {
            for($i = 0; $i < count($this->hrs_format); $i++) {
                $activities = [];
                $hourCarbon = Carbon::createFromFormat('H:i:s', $this->hrs_format[$i])->format('H:00:00');
                foreach ($orders as $order) {
                    $orderTime = Carbon::createFromFormat('H:i:s', $order->start_time)->format('H:00:00');
                    if ($hourCarbon == $orderTime) {
                        $activities[] = $order;
                    }
                }
                $daily_program[] = [
                    'hour' => Carbon::parse($this->hrs_format[$i])->format('H:i'),
                    'activities' => $activities,
                ];
            
            }
        }
        $error = null;
        
        $warning = null;

        return view(
            'dailyprogram.index',
            compact(
                'daily_program',
                'customers',
                'order_services',
                'services',
                'status',
                'date',

            )
        );
    }
    public function get_dailyprogram(Request $request)
    {
        $daily_program = [];
        $customers = Customer::all();
        $order_services = OrderService::all();
        $services = Service::all();
        $status = OrderStatus::all();
        $date = $request->input('schedule_date');
        $orders = Order::where('programmed_date', Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d'))->orderBy('start_time')->get();
        $hrs_array = $this->hrs_format;

        if($orders) {
            for($i = 0; $i < count($this->hrs_format); $i++) {
                $activities = [];
                $hourCarbon = Carbon::createFromFormat('H:i:s', $this->hrs_format[$i])->format('H:00:00');
                foreach ($orders as $order) {
                    $orderTime = Carbon::createFromFormat('H:i:s', $order->start_time)->format('H:00:00');
                    if ($hourCarbon == $orderTime) {
                        $activities[] = $order;
                    }
                }
                $daily_program[] = [
                    'hour' => Carbon::parse($this->hrs_format[$i])->format('H:i'),
                    'activities' => $activities,
                ];
            
            }
        }
        $error = null;
        $success = null;
        $warning = null;

        return view(
            'dailyprogram.index',
            compact(
                'daily_program',
                'customers',
                'order_services',
                'services',
                'status',
                'date',
                'error',
                'success',
                'warning'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
