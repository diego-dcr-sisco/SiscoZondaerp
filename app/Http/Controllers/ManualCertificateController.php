<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ManualCertificateController extends Controller
{
    public function index(): View
    {
        $navigation = [
            'Certificado' => route('report.manual-certificate.index'),
            'Cotizacion' => route('report.manual-quotation.index'),
        ];

        session()->flash('warning', 'Esta herramienta es para generar certificados de servicio de forma manual, sin guardar datos en la base de datos. Asegúrate de ingresar toda la información correctamente antes de generar el PDF, ya que no habrá forma de recuperarla posteriormente.');

        return view('report.manual-certificate.index', [
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
                    'payload' => 'El JSON es invalido. Verifica el formato antes de generar el certificado.',
                ]);
        }

        $data = $this->buildCertificateData($payload);

        $pdf = Pdf::loadView('report.pdf.certificate', $data)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
        ]);

        return $pdf->stream($data['filename']);
    }

    private function resolvePayload(Request $request): ?array
    {
        if ($request->filled('title') || $request->filled('customer_name') || $request->filled('branch_name')) {
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
        $startDate = $request->input('start_date', Carbon::now()->format('d-m-Y'));
        $startTime = $request->input('start_time', '09:00');
        $endDate = $request->input('end_date', Carbon::now()->format('d-m-Y'));
        $endTime = $request->input('end_time', '10:00');

        $serviceNames = Arr::wrap($request->input('services_name', []));
        $serviceTexts = Arr::wrap($request->input('services_text', []));
        $services = [];

        foreach ($serviceNames as $idx => $name) {
            $cleanName = trim((string) $name);
            $cleanText = trim((string) ($serviceTexts[$idx] ?? ''));

            if ($cleanName === '' && $cleanText === '') {
                continue;
            }

            $services[] = [
                'name' => $cleanName !== '' ? $cleanName : 'Servicio',
                'text' => $cleanText !== '' ? nl2br(e($cleanText)) : '',
            ];
        }

        $products = [];
        $productNames = Arr::wrap($request->input('products_name', []));
        $activeIngredients = Arr::wrap($request->input('products_active_ingredient', []));
        $registerNumbers = Arr::wrap($request->input('products_no_register', []));
        $safetyPeriods = Arr::wrap($request->input('products_safety_period', []));
        $methods = Arr::wrap($request->input('products_application_method', []));
        $dosages = Arr::wrap($request->input('products_dosage', []));
        $amounts = Arr::wrap($request->input('products_amount', []));
        $metrics = Arr::wrap($request->input('products_metric', []));
        $lots = Arr::wrap($request->input('products_lot', []));

        foreach ($productNames as $idx => $name) {
            $cleanName = trim((string) $name);
            $cleanAmount = trim((string) ($amounts[$idx] ?? ''));

            if ($cleanName === '' && $cleanAmount === '') {
                continue;
            }

            $products[] = [
                'name' => $cleanName !== '' ? $cleanName : '-',
                'active_ingredient' => trim((string) ($activeIngredients[$idx] ?? '-')),
                'no_register' => trim((string) ($registerNumbers[$idx] ?? '-')),
                'safety_period' => trim((string) ($safetyPeriods[$idx] ?? '-')),
                'application_method' => trim((string) ($methods[$idx] ?? '-')),
                'dosage' => trim((string) ($dosages[$idx] ?? '-')),
                'amount' => $cleanAmount !== '' ? $cleanAmount : '-',
                'metric' => trim((string) ($metrics[$idx] ?? '')),
                'lot' => trim((string) ($lots[$idx] ?? '-')),
            ];
        }

        $customerSignatureBase64 = $this->resolveSignatureBase64(
            $request,
            'customer_signature_file',
            'customer_signature_base64'
        );

        $technicianSignatureBase64 = $this->resolveSignatureBase64(
            $request,
            'technician_signature_file',
            'technician_signature_base64'
        );

        $photoEvidences = $this->buildPhotoEvidencesFromForm($request);

        return [
            'title' => $request->input('title', 'Certificado de Servicio Manual'),
            'filename' => $request->input('filename', ''),
            'order' => [
                'programmed_date' => $request->input('programmed_date', Carbon::now()->format('d-m-Y')),
                'start' => trim($startDate . ' - ' . $startTime),
                'end' => trim($endDate . ' - ' . $endTime),
            ],
            'branch' => [
                'name' => $request->input('branch_name', 'SISCOPLAGAS'),
                'sede' => $request->input('branch_sede', '-'),
                'address' => $request->input('branch_address', '-'),
                'email' => $request->input('branch_email', '-'),
                'phone' => $request->input('branch_phone', '-'),
                'no_license' => $request->input('branch_no_license', '-'),
            ],
            'customer' => [
                'name' => $request->input('customer_name', '-'),
                'address' => $request->input('customer_address', '-'),
                'phone' => $request->input('customer_phone', '-'),
                'social_reason' => $request->input('customer_social_reason', '-'),
                'city' => $request->input('customer_city', '-'),
                'state' => $request->input('customer_state', '-'),
                'rfc' => $request->input('customer_rfc', '-'),
                'signed_by' => $request->input('customer_signed_by', '-'),
                'signature_base64' => $customerSignatureBase64,
            ],
            'technician' => [
                'name' => $request->input('technician_name', '-'),
                'rfc' => $request->input('technician_rfc', '-'),
                'signature_base64' => $technicianSignatureBase64,
            ],
            'services' => $services,
            'products' => [
                'data' => $products,
            ],
            'reviews' => [],
            'notes' => nl2br(e((string) $request->input('notes', 'Sin notas'))),
            'recommendations' => nl2br(e((string) $request->input('recommendations', 'Sin recomendaciones'))),
            'photo_evidences' => $photoEvidences,
        ];
    }

    private function buildCertificateData(array $payload): array
    {
        $order = is_array($payload['order'] ?? null) ? $payload['order'] : [];
        $branch = is_array($payload['branch'] ?? null) ? $payload['branch'] : [];
        $customer = is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
        $technician = is_array($payload['technician'] ?? null) ? $payload['technician'] : [];

        $services = is_array($payload['services'] ?? null) ? $payload['services'] : [];
        $services = array_map(function ($service) {
            if (!is_array($service)) {
                return ['name' => 'Servicio', 'text' => ''];
            }

            return [
                'name' => $service['name'] ?? 'Servicio',
                'text' => $service['text'] ?? '',
            ];
        }, $services);

        $productHeaders = [
            'Nombre comercial',
            'Materia activa',
            'No Registro',
            'Plazo seguridad',
            'Metodo de aplicacion',
            'Dosificacion',
            'Consumo',
            'Lote',
        ];

        $products = is_array($payload['products'] ?? null) ? $payload['products'] : [];
        $productRows = is_array($products['data'] ?? null) ? $products['data'] : [];
        $productRows = array_map(function ($row) {
            if (!is_array($row)) {
                return [
                    'name' => '-',
                    'active_ingredient' => '-',
                    'no_register' => '-',
                    'safety_period' => '-',
                    'application_method' => '-',
                    'dosage' => '-',
                    'amount' => '-',
                    'metric' => '',
                    'lot' => '-',
                ];
            }

            return [
                'name' => $row['name'] ?? '-',
                'active_ingredient' => $row['active_ingredient'] ?? '-',
                'no_register' => $row['no_register'] ?? '-',
                'safety_period' => $row['safety_period'] ?? '-',
                'application_method' => $row['application_method'] ?? '-',
                'dosage' => $row['dosage'] ?? '-',
                'amount' => $row['amount'] ?? '-',
                'metric' => $row['metric'] ?? '',
                'lot' => $row['lot'] ?? '-',
            ];
        }, $productRows);

        $photoEvidences = $this->normalizePhotoEvidences($payload['photo_evidences'] ?? []);

        return [
            'title' => $payload['title'] ?? 'Certificado de Servicio Manual',
            'filename' => $this->buildFilename($payload['filename'] ?? null),
            'order' => [
                'programmed_date' => $order['programmed_date'] ?? Carbon::now()->format('d-m-Y'),
                'start' => $order['start'] ?? Carbon::now()->format('d-m-Y') . ' - 09:00',
                'end' => $order['end'] ?? Carbon::now()->format('d-m-Y') . ' - 10:00',
                'notes' => $order['notes'] ?? '',
            ],
            'branch' => [
                'name' => $branch['name'] ?? 'SISCOPLAGAS',
                'sede' => $branch['sede'] ?? '-',
                'address' => $branch['address'] ?? '-',
                'email' => $branch['email'] ?? '-',
                'phone' => $branch['phone'] ?? '-',
                'no_license' => $branch['no_license'] ?? '-',
            ],
            'customer' => [
                'name' => $customer['name'] ?? '-',
                'address' => $customer['address'] ?? '-',
                'email' => $customer['email'] ?? '-',
                'phone' => $customer['phone'] ?? '-',
                'social_reason' => $customer['social_reason'] ?? '-',
                'city' => $customer['city'] ?? '-',
                'state' => $customer['state'] ?? '-',
                'rfc' => $customer['rfc'] ?? '-',
                'signed_by' => $customer['signed_by'] ?? '-',
                'signature_base64' => $this->normalizeSignatureBase64($customer['signature_base64'] ?? ''),
            ],
            'technician' => [
                'name' => $technician['name'] ?? '-',
                'rfc' => $technician['rfc'] ?? '-',
                'signature_base64' => $this->normalizeSignatureBase64($technician['signature_base64'] ?? ''),
            ],
            'services' => $services,
            'products' => [
                'headers' => is_array($products['headers'] ?? null) && count($products['headers']) > 0
                    ? $products['headers']
                    : $productHeaders,
                'data' => $productRows,
            ],
            'reviews' => is_array($payload['reviews'] ?? null) ? $payload['reviews'] : [],
            'notes' => $payload['notes'] ?? 'Sin notas',
            'recommendations' => $payload['recommendations'] ?? 'Sin recomendaciones',
            'photo_evidences' => $photoEvidences,
        ];
    }

    private function buildFilename(?string $customFilename): string
    {
        if (is_string($customFilename) && trim($customFilename) !== '') {
            return $this->sanitizeFilename($customFilename);
        }

        return 'certificado_manual_' . now()->format('Ymd_His') . '.pdf';
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $filename) ?? 'certificado_manual';
        $filename = preg_replace('/\s+/', '_', trim($filename)) ?? 'certificado_manual';

        if ($filename === '') {
            $filename = 'certificado_manual';
        }

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return $filename;
    }

    private function resolveSignatureBase64(Request $request, string $fileField, string $base64Field): string
    {
        $file = $request->file($fileField);
        if ($file instanceof UploadedFile && $file->isValid()) {
            $fromFile = $this->fileToDataUri($file);
            if ($fromFile !== '') {
                return $fromFile;
            }
        }

        return $this->normalizeSignatureBase64((string) $request->input($base64Field, ''));
    }

    private function fileToDataUri(UploadedFile $file): string
    {
        $mimeType = strtolower((string) $file->getMimeType());
        $allowedMimeTypes = [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/webp',
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return '';
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return '';
        }

        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false || $raw === '') {
            return '';
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($raw);
    }

    private function normalizeSignatureBase64(string $signature): string
    {
        $signature = trim($signature);
        if ($signature === '') {
            return '';
        }

        if (preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/i', $signature) === 1) {
            return $signature;
        }

        $decoded = base64_decode($signature, true);
        if ($decoded === false) {
            return '';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = strtolower((string) $finfo->buffer($decoded));
        $allowedMimeTypes = [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/webp',
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return '';
        }

        return 'data:' . $mimeType . ';base64,' . $signature;
    }

    private function buildPhotoEvidencesFromForm(Request $request): array
    {
        $areas = Arr::wrap($request->input('evidence_area', []));
        $descriptions = Arr::wrap($request->input('evidence_description', []));
        $base64Images = Arr::wrap($request->input('evidence_image_base64', []));
        $uploadedImages = Arr::wrap($request->file('evidence_image_file', []));

        $grouped = [
            'servicio' => [],
            'notas' => [],
            'recomendaciones' => [],
            'evidencias' => [],
        ];

        foreach ($areas as $index => $areaRaw) {
            $area = $this->sanitizeEvidenceArea((string) $areaRaw);
            $description = trim((string) ($descriptions[$index] ?? ''));

            $image = '';
            $uploaded = $uploadedImages[$index] ?? null;
            if ($uploaded instanceof UploadedFile && $uploaded->isValid()) {
                $image = $this->fileToDataUri($uploaded);
            }

            if ($image === '') {
                $image = $this->normalizeSignatureBase64((string) ($base64Images[$index] ?? ''));
            }

            if ($image === '') {
                continue;
            }

            $grouped[$area][] = [
                'description' => $description !== '' ? $description : 'Evidencia fotografica',
                'image' => $image,
            ];
        }

        return $grouped;
    }

    private function sanitizeEvidenceArea(string $area): string
    {
        $area = trim(strtolower($area));
        $allowed = ['servicio', 'notas', 'recomendaciones', 'evidencias'];

        return in_array($area, $allowed, true) ? $area : 'evidencias';
    }

    private function normalizePhotoEvidences(mixed $rawPhotoEvidences): array
    {
        $normalized = [
            'servicio' => [],
            'notas' => [],
            'recomendaciones' => [],
            'evidencias' => [],
        ];

        if (!is_array($rawPhotoEvidences)) {
            return $normalized;
        }

        foreach ($normalized as $area => $items) {
            $records = is_array($rawPhotoEvidences[$area] ?? null) ? $rawPhotoEvidences[$area] : [];

            foreach ($records as $record) {
                if (!is_array($record)) {
                    continue;
                }

                $description = trim((string) ($record['description'] ?? 'Evidencia fotografica'));
                $image = trim((string) ($record['image'] ?? ''));

                if ($image === '') {
                    continue;
                }

                $normalized[$area][] = [
                    'description' => $description !== '' ? $description : 'Evidencia fotografica',
                    'image' => $image,
                ];
            }
        }

        return $normalized;
    }

    private function samplePayload(): array
    {
        return [
            'title' => 'Certificado de Servicio M-001',
            'filename' => 'certificado_manual_demo.pdf',
            'order' => [
                'programmed_date' => '09-04-2026',
                'start' => '09-04-2026 - 09:00',
                'end' => '09-04-2026 - 10:00',
            ],
            'branch' => [
                'name' => 'SISCOPLAGAS',
                'sede' => 'Sucursal Centro',
                'address' => 'Av. Principal 100, CDMX',
                'email' => 'contacto@siscoplagas.com',
                'phone' => '55 1234 5678',
                'no_license' => 'ROESB-001',
            ],
            'customer' => [
                'name' => 'Cliente Demo SA de CV',
                'address' => 'Calle Demo 123',
                'phone' => '55 1111 2222',
                'social_reason' => 'Cliente Demo SA de CV',
                'city' => 'CDMX',
                'state' => 'CDMX',
                'rfc' => 'XAXX010101000',
                'signed_by' => 'Nombre del cliente',
                'signature_base64' => '',
            ],
            'technician' => [
                'name' => 'Tecnico Demo',
                'rfc' => 'DEMO800101AAA',
                'signature_base64' => '',
            ],
            'services' => [
                [
                    'name' => 'Control de plagas general',
                    'text' => '<p>Aplicacion preventiva en zonas criticas.</p>',
                ],
            ],
            'products' => [
                'data' => [
                    [
                        'name' => 'Producto A',
                        'active_ingredient' => 'Ingrediente A',
                        'no_register' => 'REG-123',
                        'safety_period' => '24 horas',
                        'application_method' => 'Aspersion',
                        'dosage' => '10 ml',
                        'amount' => '1',
                        'metric' => 'L',
                        'lot' => 'L-001',
                    ],
                ],
            ],
            'reviews' => [],
            'notes' => '<p>Sin notas adicionales.</p>',
            'recommendations' => '<p>Mantener limpieza continua en areas de produccion.</p>',
            'photo_evidences' => [
                'servicio' => [],
                'notas' => [],
                'recomendaciones' => [],
                'evidencias' => [],
            ],
        ];
    }
}