<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientReportController_br extends Controller
{
    public function index()
    {
        return view('report.clients_br.index');
    }

    public function export(Request $request)
    {
        // 1. Validamos los datos de la vista
        $filters = $request->validate([
            'date_range'  => 'required|string', 
            'client_type' => 'required|in:all,new,recurrent', 
            'metrics'     => 'nullable|array' 
        ]);

        // 2. Rompemos las fechas con Carbon
        [$startDate, $endDate] = explode(' - ', $filters['date_range']);

        $reportService = new \App\Services\CustomerReportService_br();
        
        $customers = $reportService->getReportData(
            $startDate, 
            $endDate, 
            $filters['client_type'], 
            $filters['metrics'] ?? []
        );

        $metrics = $filters['metrics'] ?? [];

        // 4. CONFIGURACIÓN NATIVA PARA ENGAÑAR AL NAVEGADOR (Bypass de Maatwebsite)
        $filename = 'reporte_clientes_' . date('Ymd_His') . '.xls';
        
        // Cabeceras HTTP para forzar la descarga de un Excel
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        // Agregamos el BOM de UTF-8 para que Excel lea bien los acentos y la Ñ
        echo "\xEF\xBB\xBF"; 

        // 5. Pintamos la estructura de la tabla que Excel convertirá en columnas automáticamente
        echo '<table border="1">';
        echo '<thead style="background-color: #0d6efd; color: white; font-weight: bold;">';
        echo '<tr>';
        echo '<th>ID Cliente</th>';
        echo '<th>Nombre / Razón Social</th>';
        echo '<th>Tipo de Cliente</th>';
        
        // Encabezados dinámicos según los checkboxes del PDF
        if (in_array('inc_orders_count', $metrics)) echo '<th>Cant. Órdenes de Servicio</th>';
        if (in_array('inc_has_devices', $metrics))  echo '<th>¿Cuenta con Dispositivos?</th>';
        if (in_array('inc_devices_count', $metrics)) echo '<th>Cant. Total Dispositivos</th>';
        if (in_array('inc_device_types', $metrics))  echo '<th>Tipos de Dispositivos</th>';
        if (in_array('inc_pests_count', $metrics))   echo '<th>Cant. Plagas Asociadas (e)</th>';
        if (in_array('inc_pest_types', $metrics))    echo '<th>Tipos de Plagas Detectadas (f)</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // 6. Llenamos las filas con los clientes reales de XAMPP
        foreach ($customers as $customer) {
            echo '<tr>';
            // El ID real en tu objeto es 'id'
            echo '<td>' . ($customer->id ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($customer->name ?? 'N/A') . '</td>';
            
            // El tipo real que calcula tu servicio es 'calculated_type'
            $tipoCliente = ($customer->calculated_type ?? 'new') === 'new' ? 'Nuevo' : 'Recurrente';
            echo '<td>' . $tipoCliente . '</td>';
            
            if (in_array('inc_orders_count', $metrics)) {
                // Tu servicio inyecta 'total_orders_in_range'
                echo '<td>' . ($customer->total_orders_in_range ?? 0) . '</td>';
            }
            if (in_array('inc_has_devices', $metrics)) {
                // Si el conteo es mayor a 0, entonces sí tiene dispositivos
                $tieneDispositivos = ($customer->devices_count ?? 0) > 0 ? 'Sí' : 'No';
                echo '<td>' . $tieneDispositivos . '</td>';
            }
            if (in_array('inc_devices_count', $metrics)) {
                echo '<td>' . ($customer->devices_count ?? 0) . '</td>';
            }
            if (in_array('inc_device_types', $metrics)) {
                echo '<td>' . htmlspecialchars($customer->device_types ?? 'N/A') . '</td>';
            }
            if (in_array('inc_pests_count', $metrics)) {
                echo '<td>' . ($customer->pests_count ?? 0) . '</td>';
            }
            if (in_array('inc_pest_types', $metrics)) {
                echo '<td>' . htmlspecialchars($customer->pest_types ?? 'Ninguna') . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        exit;
    }
}