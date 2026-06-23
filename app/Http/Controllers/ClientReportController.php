<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ClientReportExport;
use App\Services\ClientReportService;
use Maatwebsite\Excel\Facades\Excel;

class ClientReportController extends Controller
{
    public function index()
    {
        return view('report.client.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);

        $startDate = $request->from;
        $endDate   = $request->to;
        $type      = $request->type ?? 'all';
        $metrics   = $request->metrics ?? [];

        $service   = new ClientReportService();
        $customers = $service->getReportData($startDate, $endDate, $type, $metrics);


        $filename  = 'reporte_clientes_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new ClientReportExport($customers, $metrics),
            $filename
        );
    }
}