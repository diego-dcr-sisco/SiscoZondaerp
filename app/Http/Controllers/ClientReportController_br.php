<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ClientReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ClientReportController_br extends Controller
{
    // Muestra la pantalla con el formulario de filtros
    public function index()
    {
        return view('reports.clients.index');
    }

    // Recibe los datos del formulario al dar clic en "Exportar"
    public function export(Request $request)
    {
        // Validamos los datos obligatorios del formulario
        $filters = $request->validate([
            'date_range'  => 'required|string', 
            'client_type' => 'required|in:all,new,recurrent', 
            'metrics'     => 'nullable|array' 
        ]);

        // Rompemos el rango de fechas en dos variables
        [$startDate, $endDate] = explode(' - ', $filters['date_range']);

        // Descargamos el reporte enviándole los filtros limpios a la clase de Exportación
        return Excel::download(
            new ClientReportExport($startDate, $endDate, $filters['client_type'], $filters['metrics'] ?? []), 
            'reporte_clientes_nuevos_recurrentes.xlsx'
        );
    }
}