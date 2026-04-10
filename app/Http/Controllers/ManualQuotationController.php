<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ManualQuotationController extends Controller
{
    public function index(): View
    {
        $navigation = [
            'Certificado' => route('report.manual-certificate.index'),
            'Cotizacion' => route('report.manual-quotation.index'),
        ];

                session()->flash('warning', 'Esta herramienta es para generar certificados de servicio de forma manual, sin guardar datos en la base de datos. Asegúrate de ingresar toda la información correctamente antes de generar el PDF, ya que no habrá forma de recuperarla posteriormente.');

        return view('report.manual-quotation.index', [
            'sampleJson' => json_encode($this->samplePayload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'navigation' => $navigation,
        ]);
    }

    public function generate(Request $request)
    {
        $payload = $this->resolvePayload($request);

        if (!is_array($payload)) {
            return back()
                ->withInput()
                ->withErrors([
                    'payload' => 'El JSON es invalido. Verifica el formato antes de generar la cotizacion.',
                ]);
        }

        $data = $this->buildQuotationData($payload);

        $pdf = Pdf::loadView('report.pdf.quotation', $data)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
        ]);

        return $pdf->stream($data['filename']);
    }

    private function resolvePayload(Request $request): ?array
    {
        if ($request->filled('title') || $request->filled('customer_name') || $request->filled('company_name')) {
            return $this->buildPayloadFromForm($request);
        }

        $rawPayload = $request->input('payload');

        if (is_string($rawPayload) && trim($rawPayload) !== '') {
            $decoded = json_decode($rawPayload, true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
        }

        if ($request->isJson()) {
            $jsonPayload = $request->json()->all();
            return is_array($jsonPayload) && !empty($jsonPayload) ? $jsonPayload : null;
        }

        return null;
    }

    private function buildPayloadFromForm(Request $request): array
    {
        $serviceNames = Arr::wrap($request->input('services_name', []));
        $serviceDescriptions = Arr::wrap($request->input('services_description', []));
        $serviceQty = Arr::wrap($request->input('services_qty', []));
        $serviceUnits = Arr::wrap($request->input('services_unit', []));
        $serviceUnitPrices = Arr::wrap($request->input('services_unit_price', []));

        $services = [];
        foreach ($serviceNames as $idx => $name) {
            $cleanName = trim((string) $name);
            $cleanQty = $this->toFloat($serviceQty[$idx] ?? 0);
            $cleanUnitPrice = $this->toFloat($serviceUnitPrices[$idx] ?? 0);

            if ($cleanName === '' && $cleanQty <= 0 && $cleanUnitPrice <= 0) {
                continue;
            }

            $services[] = [
                'name' => $cleanName !== '' ? $cleanName : 'Servicio',
                'description' => trim((string) ($serviceDescriptions[$idx] ?? '')),
                'qty' => $cleanQty > 0 ? $cleanQty : 1,
                'unit' => trim((string) ($serviceUnits[$idx] ?? 'servicio')),
                'unit_price' => $cleanUnitPrice,
            ];
        }

        return [
            'title' => $request->input('title', 'Cotizacion de Servicios'),
            'filename' => $request->input('filename', ''),
            'quote_no' => $request->input('quote_no', 'COT-' . now()->format('Ymd-His')),
            'issued_date' => $request->input('issued_date', Carbon::now()->format('d-m-Y')),
            'valid_until' => $request->input('valid_until', Carbon::now()->addDays(15)->format('d-m-Y')),
            'currency' => strtoupper((string) $request->input('currency', 'MXN')),
            'tax_percent' => $this->toFloat($request->input('tax_percent', 16)),
            'payment_terms' => $request->input('payment_terms', '50% anticipo y 50% contra entrega.'),
            'delivery_time' => $request->input('delivery_time', '5 dias habiles a partir de la aprobacion.'),
            'company' => [
                'name' => $request->input('company_name', 'SISCOPLAGAS'),
                'rfc' => $request->input('company_rfc', '-'),
                'address' => $request->input('company_address', '-'),
                'email' => $request->input('company_email', '-'),
                'phone' => $request->input('company_phone', '-'),
            ],
            'customer' => [
                'name' => $request->input('customer_name', '-'),
                'company' => $request->input('customer_company', '-'),
                'attn' => $request->input('customer_attn', '-'),
                'address' => $request->input('customer_address', '-'),
                'email' => $request->input('customer_email', '-'),
                'phone' => $request->input('customer_phone', '-'),
                'rfc' => $request->input('customer_rfc', '-'),
            ],
            'services' => $services,
            'notes' => nl2br(e((string) $request->input('notes', ''))),
            'conditions' => nl2br(e((string) $request->input('conditions', 'Precios sujetos a cambio sin previo aviso.'))),
        ];
    }

    private function buildQuotationData(array $payload): array
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
            'filename' => $this->buildFilename($payload['filename'] ?? null),
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

    private function buildFilename(?string $customFilename): string
    {
        if (is_string($customFilename) && trim($customFilename) !== '') {
            return $this->sanitizeFilename($customFilename);
        }

        return 'cotizacion_manual_' . now()->format('Ymd_His') . '.pdf';
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $filename) ?? 'cotizacion_manual';
        $filename = preg_replace('/\s+/', '_', trim($filename)) ?? 'cotizacion_manual';

        if ($filename === '') {
            $filename = 'cotizacion_manual';
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

    private function samplePayload(): array
    {
        return [
            'title' => 'Cotizacion de Servicios de Control de Plagas',
            'filename' => 'cotizacion_demo.pdf',
            'quote_no' => 'COT-20260409-001',
            'issued_date' => '09-04-2026',
            'valid_until' => '24-04-2026',
            'currency' => 'MXN',
            'tax_percent' => 16,
            'payment_terms' => '50% anticipo y 50% contra entrega del servicio.',
            'delivery_time' => 'Inicio dentro de las 48 horas posteriores a la aprobacion.',
            'company' => [
                'name' => 'SISCOPLAGAS',
                'rfc' => 'SIS010101ABC',
                'address' => 'Av. Principal 100, CDMX',
                'email' => 'ventas@siscoplagas.com',
                'phone' => '55 1234 5678',
            ],
            'customer' => [
                'name' => 'Cliente Demo',
                'company' => 'Cliente Demo SA de CV',
                'attn' => 'Compras',
                'address' => 'Calle Demo 123, CDMX',
                'email' => 'compras@cliente-demo.com',
                'phone' => '55 0000 1111',
                'rfc' => 'XAXX010101000',
            ],
            'services' => [
                [
                    'name' => 'Servicio integral de control de plagas',
                    'description' => 'Inspeccion, aplicacion y seguimiento mensual en areas criticas.',
                    'qty' => 1,
                    'unit' => 'servicio',
                    'unit_price' => 4500,
                ],
                [
                    'name' => 'Monitoreo y reporteria',
                    'description' => 'Reporte fotografico y plan de mejora al cierre de cada visita.',
                    'qty' => 1,
                    'unit' => 'servicio',
                    'unit_price' => 1200,
                ],
            ],
            'notes' => 'La cotizacion incluye materiales y mano de obra.',
            'conditions' => 'Vigencia de 15 dias naturales. No incluye trabajos fuera de horario.',
        ];
    }
}
