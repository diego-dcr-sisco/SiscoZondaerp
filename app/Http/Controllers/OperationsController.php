<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Order;
use App\Models\OrderTechnician;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;

class OperationsController extends Controller
{
    /**
     * Mostrar el dashboard de control de operaciones
     */
    public function index(Request $request): View
    {
        // Obtener todos los técnicos activos
        $technicians = User::where('role_id', 3)
            ->where('status_id', 2) // Activos
            ->orderBy('name')
            ->get();

        // Tamaño de paginación (por defecto 50)
        $size = $request->input('size', 50);

        // Inicializar query de órdenes pendientes
        $query = Order::with(['customer', 'customer.matrix', 'status', 'technicians.user', 'services', 'closeUser'])
            ->where('status_id', 1); // 1 = Pendiente

        // Filtrar por fechas si se proporcionan
        if ($request->filled('start_date')) {
            $query->where('programmed_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('programmed_date', '<=', $request->end_date);
        }

        // Filtrar por técnicos si se proporcionan (uno o varios)
        if ($request->filled('technician_ids')) {
            $technicianIds = is_array($request->technician_ids) 
                ? $request->technician_ids 
                : [$request->technician_ids];
            
            // Obtener los IDs de técnicos de la tabla technician
            $technicianModels = Technician::whereIn('user_id', $technicianIds)
                ->pluck('id')
                ->toArray();
            
            if (!empty($technicianModels)) {
                $query->whereHas('techniciansScope', function ($q) use ($technicianModels) {
                    $q->whereIn('technician_id', $technicianModels);
                });
            }
        }

        // Obtener las órdenes con paginación
        $orders = $query->orderBy('programmed_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($size);

        // Mantener parámetros de búsqueda en paginación
        $orders->appends($request->except('page'));

        return view('dashboard.operations.index', compact('orders', 'technicians'));
    }
}
