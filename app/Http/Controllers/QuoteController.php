<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

use App\Models\Quote;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Tracking;
use App\Models\QuotePdfSnapshot;
use App\Enums\QuoteStatus;
use App\Enums\QuotePriority;

class QuoteController extends Controller
{
    private $files_path = 'quotes/files/';

    private $historyColumns = [
        'Priority' => 'Prioridad',
        'Status' => 'Estado',
        'Value' => 'Valor'
    ];

    public function index(string $id, string $class)
    {
        $quotes_data = [];
        $customer = null;

        if ($class == 'customer') {
            $customer = Customer::findOrFail($id);
        }

        if ($class == 'lead') {
            $customer = Lead::findOrFail($id);
        }

        if (!$customer) {
            return redirect()->back()->with('error', 'Cliente o cliente potencial no encontrado.');
        }

        $services = Service::select('id', 'name')->orderBy('name')->get();
        $quote_status = QuoteStatus::cases();
        $quote_priority = QuotePriority::cases();

        if ($customer::class == Lead::class) {
            $navigation = [
                'Cliente potencial' => route('customer.edit.lead', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'lead']),
            ];
        } else {
            $navigation = [
                'Sede' => route('customer.edit.sede', ['id' => $customer->id]),
                'Archivos' => route('customer.show.sede.files', ['id' => $customer->id]),
                'Planos' => route('customer.show.sede.floorplans', ['id' => $customer->id]),
                'Portal' => route('customer.show.sede.portal', ['id' => $customer->id]),
                'Areas de aplicación' => route('customer.show.sede.areas', ['id' => $customer->id]),
                //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer->id, 'class' => 'customer']),
                'Estadisticas' => route('customer.graphics', ['id' => $customer->id]),
            ];
        }

        $quotes = Quote::where('model_id', $customer->id)
            ->where('model_type', Customer::class)
            ->with(['service', 'latestPdfSnapshot'])
            ->orderBy('created_at', 'desc')
            ->get();

        $customer = [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => Customer::class,
        ];

        return view('quote.index', compact('customer', 'services', 'quotes', 'quote_status', 'quote_priority', 'navigation', 'quotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $quote = new Quote();
        $quote->fill($request->except(['file']));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'quote_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = 'quotes/' . now()->format('Y/m');
            $filePath = $directory . '/' . $fileName;
            $fileContent = file_get_contents($file->getRealPath());

            Storage::disk('public')->put($filePath, $fileContent);
            $quote->file = $filePath;
        }

        $quote->save();
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
        $quote = Quote::findOrFail($id);
        $customer_id = $quote->model_id;
        $class = $quote->model_type == Customer::class ? 'customer' : 'lead';

        if ($quote->model_type == Lead::class) {
            $navigation = [
                'Cliente potencial' => route('customer.edit.lead', ['id' => $customer_id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer_id, 'class' => 'lead']),
            ];
        } else {
            $navigation = [
                'Sede' => route('customer.edit.sede', ['id' => $customer_id]),
                'Archivos' => route('customer.show.sede.files', ['id' => $customer_id]),
                'Planos' => route('customer.show.sede.floorplans', ['id' => $customer_id]),
                'Portal' => route('customer.show.sede.portal', ['id' => $customer_id]),
                'Areas de aplicación' => route('customer.show.sede.areas', ['id' => $customer_id]),
                //'Seguimientos' => route('customer.show.sede.trackings', ['id' => $customer->id]),
                'Cotizaciones' => route('customer.quote', ['id' => $customer_id, 'class' => 'customer']),
            ];
        }

        return view('quote.edit', [
            'quote' => $quote,
            'services' => Service::select('id', 'name')->orderBy('name')->get(),
            'service_options' => Service::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                ])
                ->all(),
            'quote_status' => QuoteStatus::cases(),
            'quote_priority' => QuotePriority::cases(),
            'histories' => Quote::findOrFail($id)->histories()->orderBy('created_at', 'desc')->get(),
            'historyColumns' => $this->historyColumns,
            'navigation' => $navigation,
            'class' => $class
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->update($request->except(['file']));

        if ($request->hasFile('file')) {
            // Eliminar el archivo anterior si existe
            if ($quote->file && Storage::disk('public')->exists($quote->file)) {
                Storage::disk('public')->delete($quote->file);
            }

            $file = $request->file('file');
            $fileName = 'quote_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = 'quotes/' . now()->format('Y/m');
            $filePath = $directory . '/' . $fileName;
            $fileContent = file_get_contents($file->getRealPath());

            Storage::disk('public')->put($filePath, $fileContent);
            $quote->file = $filePath;
        }

        $quote->save();
        return back()->with('success', 'Cotización actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quote = Quote::findOrFail($id);
        if ($quote) {
            // Eliminar el archivo asociado si existe
            if ($quote->file && Storage::disk('public')->exists($quote->file)) {
                Storage::disk('public')->delete($quote->file);
            }
            $quote->delete();
            return back()->with('success', 'Cotización eliminada correctamente.');
        }
        return back()->with('error', 'Cotización no encontrada.');
    }

    public function download(string $id)
    {
        try {
            $quote = Quote::findOrFail($id);

            if (!$quote->file || !Storage::disk('public')->exists($quote->file)) {
                return response()->json(['error' => 'El archivo no existe.'], 404);
            }

            $fullPath = storage_path('app/public/' . $quote->file);
            return response()->download($fullPath);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Registro de archivo no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al descargar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function storePdfSnapshot(Request $request, string $id)
    {
        $quote = Quote::with(['service', 'model'])->findOrFail($id);
        $payload = $this->buildPayloadFromQuote($quote, $request);

        $nextVersion = ((int) QuotePdfSnapshot::where('quote_id', $quote->id)->max('version')) + 1;

        QuotePdfSnapshot::create([
            'quote_id' => $quote->id,
            'user_id' => Auth::id(),
            'version' => $nextVersion,
            'title' => $payload['title'],
            'quote_no' => $payload['quote_no'],
            'currency' => $payload['currency'],
            'issued_date' => $this->toDateForDb($payload['issued_date']),
            'valid_until' => $this->toDateForDb($payload['valid_until']),
            'tax_percent' => $payload['tax_percent'],
            'payload' => $payload,
        ]);

        return back()->with('success', 'Datos de PDF de la cotizacion guardados correctamente.');
    }

    public function generatePdf(Request $request, string $id)
    {
        $quote = Quote::with(['service', 'model'])->findOrFail($id);

        $snapshot = QuotePdfSnapshot::where('quote_id', $quote->id)->latest('id')->first();
        if (!$snapshot) {
            $payload = $this->buildPayloadFromQuote($quote, $request);
            $snapshot = QuotePdfSnapshot::create([
                'quote_id' => $quote->id,
                'user_id' => Auth::id(),
                'version' => 1,
                'title' => $payload['title'],
                'quote_no' => $payload['quote_no'],
                'currency' => $payload['currency'],
                'issued_date' => $this->toDateForDb($payload['issued_date']),
                'valid_until' => $this->toDateForDb($payload['valid_until']),
                'tax_percent' => $payload['tax_percent'],
                'payload' => $payload,
            ]);
        }

        $data = $this->buildPdfData($snapshot->payload ?? []);

        $pdf = Pdf::loadView('report.pdf.quotation', $data)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
        ]);

        $pdfPath = 'quotes/generated/' . now()->format('Y/m') . '/quote_' . $quote->id . '_v' . $snapshot->version . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $snapshot->update([
            'pdf_path' => $pdfPath,
            'generated_at' => now(),
        ]);

        if ($request->boolean('download')) {
            return $pdf->download($data['filename']);
        }

        return $pdf->stream($data['filename']);
    }

    public function downloadGeneratedPdf(string $id)
    {
        $snapshot = QuotePdfSnapshot::where('quote_id', $id)
            ->whereNotNull('pdf_path')
            ->latest('id')
            ->firstOrFail();

        if (!Storage::disk('public')->exists($snapshot->pdf_path)) {
            return back()->with('error', 'No existe un PDF generado para esta cotizacion.');
        }

        $fullPath = storage_path('app/public/' . $snapshot->pdf_path);

        return response()->download($fullPath, $this->sanitizeFilename($snapshot->quote_no ?: ('cotizacion_' . $snapshot->quote_id)));
    }

    public function search()
    {

    }

    private function buildPayloadFromQuote(Quote $quote, Request $request): array
    {
        $customer = $quote->model;

        $customerName = (string) ($customer->name ?? 'Cliente');
        $customerCompany = (string) ($customer->tax_name ?? $customer->businessname ?? $customerName);
        $customerPhone = (string) ($customer->phone ?? $customer->tel ?? '-');
        $customerAddress = (string) ($customer->address ?? '-');
        $customerEmail = (string) ($customer->email ?? '-');
        $customerRfc = (string) ($customer->rfc ?? '-');

        $issuedDate = Carbon::now()->format('d-m-Y');
        $validUntil = $quote->valid_until ? Carbon::parse($quote->valid_until)->format('d-m-Y') : Carbon::now()->addDays(15)->format('d-m-Y');

        return [
            'title' => 'Cotizacion de Servicios',
            'quote_no' => 'COT-' . str_pad((string) $quote->id, 6, '0', STR_PAD_LEFT),
            'issued_date' => $issuedDate,
            'valid_until' => $validUntil,
            'currency' => 'MXN',
            'tax_percent' => 16,
            'payment_terms' => '50% anticipo y 50% contra entrega.',
            'delivery_time' => 'Segun cronograma aprobado por el cliente.',
            'company' => [
                'name' => config('app.name', 'SISCOPLAGAS'),
                'rfc' => '-',
                'address' => '-',
                'email' => '-',
                'phone' => '-',
            ],
            'customer' => [
                'name' => $customerName,
                'company' => $customerCompany,
                'attn' => $customerName,
                'address' => $customerAddress,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'rfc' => $customerRfc,
            ],
            'services' => [
                [
                    'name' => (string) ($quote->service->name ?? 'Servicio de control de plagas'),
                    'description' => (string) ($quote->comments ?? ''),
                    'qty' => 1,
                    'unit' => 'servicio',
                    'unit_price' => $this->toFloat($quote->value),
                ],
            ],
            'notes' => (string) ($quote->comments ?? ''),
            'conditions' => 'Precios sujetos a cambio sin previo aviso.',
        ];
    }

    private function buildPdfData(array $payload): array
    {
        $company = is_array($payload['company'] ?? null) ? $payload['company'] : [];
        $customer = is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
        $rawServices = is_array($payload['services'] ?? null) ? $payload['services'] : [];

        $services = [];
        $subtotal = 0.0;

        foreach ($rawServices as $service) {
            if (!is_array($service)) {
                continue;
            }

            $qty = $this->toFloat($service['qty'] ?? 0);
            $unitPrice = $this->toFloat($service['unit_price'] ?? 0);
            $lineTotal = $qty * $unitPrice;

            $services[] = [
                'name' => $service['name'] ?? 'Servicio',
                'description' => $service['description'] ?? '',
                'qty' => $qty > 0 ? $qty : 1,
                'unit' => $service['unit'] ?? 'servicio',
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineTotal;
        }

        if (empty($services)) {
            $services[] = [
                'name' => 'Servicio',
                'description' => '',
                'qty' => 1,
                'unit' => 'servicio',
                'unit_price' => 0,
                'line_total' => 0,
            ];
        }

        $taxPercent = $this->toFloat($payload['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;

        return [
            'title' => $payload['title'] ?? 'Cotizacion de Servicios',
            'filename' => $this->sanitizeFilename((string) ($payload['quote_no'] ?? ('cotizacion_' . now()->format('Ymd_His'))) . '.pdf'),
            'quote_no' => $payload['quote_no'] ?? ('COT-' . now()->format('Ymd-His')),
            'issued_date' => $payload['issued_date'] ?? Carbon::now()->format('d-m-Y'),
            'valid_until' => $payload['valid_until'] ?? Carbon::now()->addDays(15)->format('d-m-Y'),
            'currency' => strtoupper((string) ($payload['currency'] ?? 'MXN')),
            'tax_percent' => $taxPercent,
            'payment_terms' => $payload['payment_terms'] ?? '-',
            'delivery_time' => $payload['delivery_time'] ?? '-',
            'company' => [
                'name' => $company['name'] ?? 'SISCOPLAGAS',
                'rfc' => $company['rfc'] ?? '-',
                'address' => $company['address'] ?? '-',
                'email' => $company['email'] ?? '-',
                'phone' => $company['phone'] ?? '-',
            ],
            'customer' => [
                'name' => $customer['name'] ?? '-',
                'company' => $customer['company'] ?? '-',
                'attn' => $customer['attn'] ?? '-',
                'address' => $customer['address'] ?? '-',
                'email' => $customer['email'] ?? '-',
                'phone' => $customer['phone'] ?? '-',
                'rfc' => $customer['rfc'] ?? '-',
            ],
            'services' => $services,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'notes' => $payload['notes'] ?? '',
            'conditions' => $payload['conditions'] ?? '',
        ];
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $filename) ?? 'cotizacion';
        $filename = preg_replace('/\s+/', '_', trim($filename)) ?? 'cotizacion';

        if ($filename === '') {
            $filename = 'cotizacion';
        }

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return $filename;
    }

    private function toFloat(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace([',', '$', ' '], ['.', '', ''], $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function toDateForDb(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}
