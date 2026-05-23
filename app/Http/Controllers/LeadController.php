<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::where('status', '!=', 0)->orderBy('id', 'desc')->paginate(50);
        return view('customer.leads.index', compact('leads'));
    }

    public function importForm()
    {
        return view('customer.leads.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls,tsv,ods|max:5120' // 5MB máximo
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $header = array_shift($rows); // Primera fila como encabezado
            $header = array_map('strtoupper', $header); // Normalizar encabezados

            $imported = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                try {
                    // Combinar encabezados con valores
                    $record = array_combine($header, $row);
                    $data = $this->processRecord($record);

                    if (!$this->validateRecord($data)) {
                        $skipped++;
                        continue;
                    }

                    Lead::updateOrCreate(           
                        $data
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    continue;
                }
            }

            return redirect()->route('leads.index')
                ->with('success', "Importación completada: $imported leads importados, $skipped registros omitidos.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    protected function processRecord($record)
    {
        // Normalizar los nombres de las columnas para manejar diferentes formatos
        $name = trim($record['NOMBRE'] ?? $record['NOMBRE_CLIENTE'] ?? $record['CLIENTE'] ?? '');
        $phone = $this->cleanPhone($record['TELEFONO'] ?? $record['TEL'] ?? $record['CELULAR'] ?? '');
        $email = trim($record['CORREO'] ?? $record['EMAIL'] ?? $record['E-MAIL'] ?? '');
        $state = trim($record['ESTADO'] ?? $record['ESTADO DE LA REPUBLICA'] ?? $record['ENTIDAD'] ?? '');
        $service = trim($record['SECTOR'] ?? $record['TIPO CLIENTE'] ?? $record['DOMESTICO/COMERCIAL/INDUSTRIAL'] ?? '');
        $reason = trim($record['RAZON'] ?? $record['ESTADO DEL CLIENTE'] ?? $record['COMENTARIOS'] ?? 'No especificado');
        $date = trim($record['FECHA'] ?? $record['FECHA REGISTRO'] ?? $record['FECHA_CREACION'] ?? '');

        return [
            'company_category_id' => null,
            'administrative_id' => null,
            'branch_id' => null,
            'company_id' => null,
            'name' => $name,
            'address' => null,
            'city' => null,
            'zip_code' =>null, 
            'phone' => $phone ?? null,
            'email' => $email ?? null,
            'state' => $state ?? null,
            'map_location_url' => null,
            'tracking_at' => null,
            'service_type_id' => $this->mapServiceType($service),
            'reason' => $reason,
            'status' => 1,
            'created_at' => $this->parseDate($date),
            'updated_at' => now(),
        ];
    }

    protected function validateRecord($data)
    {
        return !empty($data['name']);
    }

    protected function cleanPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    protected function mapServiceType($service)
    {
        $service = trim(strtolower($service));

        $mapping = [
            'doméstico' => 1,
            'domestico' => 1,
            'negocio' => 2,
            'industrial' => 3,
            '' => null,
        ];

        return $mapping[$service] ?? null; // Valor por defecto
    }



    protected function parseDate($date)
    {
        if (empty($date)) {
            return now();
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Exception $e) {
            return now();
        }
    }

}