<?php

namespace App\Services;

use App\Models\CommercialProspect;
use App\Models\DailyTracking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class ExcelImportService
{
    /**
     * Importa un archivo Excel con múltiples hojas
     */
    public function importFile(string $filePath): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => [
                'daily_tracking' => [
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'skipped_rows' => [],
                    'empty_rows' => 0,
                    'errors' => [],
                ],
                'commercial_prospects' => [
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'skipped_rows' => [],
                    'empty_rows' => 0,
                    'errors' => [],
                ],
            ],
            'total_records' => 0,
            'import_time' => null,
        ];

        $startTime = Carbon::now();

        try {
            DB::beginTransaction();

            $spreadsheet = IOFactory::load($filePath);
            Log::info('Archivo Excel cargado para importación', [
                'file_path' => $filePath,
                'sheets' => array_map(fn ($sheet) => $sheet->getTitle(), $spreadsheet->getAllSheets()),
            ]);

            $sheets = $spreadsheet->getAllSheets();

            // Nuevo enfoque: detectar por headers primero (independiente del nombre de hoja).
            $dailySheet = $this->findFirstDailyTrackingSheet($sheets);
            if (! $dailySheet) {
                $dailySheet = $this->findSheetByName($sheets, ['Registro_Diario_CRM']);
            }

            $prospectsSheet = $this->findFirstCommercialProspectsSheet($sheets, $dailySheet);
            if (! $prospectsSheet) {
                $prospectsSheet = $this->findSheetByName($sheets, ['PROSPECTOS COMERCIALES']);
            }

            if (! $dailySheet && ! $prospectsSheet) {
                throw new \RuntimeException('No se encontraron hojas con headers compatibles para CRM y/o Prospección.');
            }

            if ($dailySheet) {
                Log::info('Iniciando importación de Registro_Diario_CRM');
                $dailyResult = $this->importDailyTrackingSheet($dailySheet);
                $result['data']['daily_tracking'] = $dailyResult;

                Log::info('Hoja Registro_Diario_CRM importada', [
                    'inserted' => $dailyResult['inserted'],
                    'updated' => $dailyResult['updated'],
                    'skipped' => $dailyResult['skipped'],
                    'errors_count' => count($dailyResult['errors']),
                ]);
            } else {
                $result['data']['daily_tracking']['errors'][] = 'No se encontró una hoja con headers compatibles para CRM.';
                Log::warning('No se encontró una hoja con headers compatibles para CRM en el archivo importado.');
            }

            if ($prospectsSheet) {
                Log::info('Iniciando importación de PROSPECTOS COMERCIALES');
                $prospectResult = $this->importCommercialProspectsSheet($prospectsSheet);
                $result['data']['commercial_prospects'] = $prospectResult;

                Log::info('Hoja PROSPECTOS COMERCIALES importada', [
                    'inserted' => $prospectResult['inserted'],
                    'updated' => $prospectResult['updated'],
                    'skipped' => $prospectResult['skipped'],
                    'errors_count' => count($prospectResult['errors']),
                ]);
            } else {
                Log::info('No se encontró una hoja con headers compatibles para Prospección en el archivo importado. Se omite su importación.');
            }

            DB::commit();

            $totalRecords =
                $result['data']['daily_tracking']['inserted'] +
                $result['data']['daily_tracking']['updated'] +
                $result['data']['commercial_prospects']['inserted'] +
                $result['data']['commercial_prospects']['updated'];
            $totalEmptyRows =
                $result['data']['daily_tracking']['empty_rows'] +
                $result['data']['commercial_prospects']['empty_rows'];
            $totalErrors = count($result['data']['daily_tracking']['errors']) + count($result['data']['commercial_prospects']['errors']);

            if ($totalRecords > 0) {
                $result['success'] = true;
                $result['message'] = "Importación completada: {$totalRecords} registros procesados";

                if ($totalEmptyRows > 0) {
                    $result['message'] .= " ({$totalEmptyRows} líneas vacías omitidas)";
                }

                if ($totalErrors > 0) {
                    $result['message'] .= " ({$totalErrors} errores)";
                }
            } else {
                if ($totalEmptyRows > 0 && $totalErrors === 0) {
                    $result['message'] = "No se insertaron registros. Se detectaron {$totalEmptyRows} líneas vacías.";
                } else {
                    $result['message'] = 'No se insertaron registros. Revisa los errores para más detalles.';
                }
            }

            $result['total_records'] = $totalRecords;
            $result['import_time'] = abs($startTime->diffInSeconds(Carbon::now()));

            Log::info('Resumen importación Excel', [
                'daily_tracking' => $result['data']['daily_tracking'],
                'commercial_prospects' => $result['data']['commercial_prospects'],
                'total_records' => $result['total_records'],
                'total_empty_rows' => $totalEmptyRows,
                'import_time_seconds' => $result['import_time'],
                'note' => 'Los prospectos comerciales se guardan en commercial_prospects y no aparecen en el index de daily-trackings.',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error en importación Excel: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $result['message'] = 'Error al procesar el archivo: ' . $e->getMessage();
            $result['success'] = false;
        }

        return $result;
    }

    /**
     * @param array<int, Worksheet> $sheets
     */
    private function findSheetByName(array $sheets, array $names): ?Worksheet
    {
        $normalizedNames = array_map(fn ($name) => $this->normalizeHeader((string) $name), $names);

        foreach ($sheets as $sheet) {
            if (in_array($this->normalizeHeader($sheet->getTitle()), $normalizedNames, true)) {
                return $sheet;
            }
        }

        return null;
    }

    private function importDailyTrackingSheet(Worksheet $sheet): array
    {
        $result = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => [], 'empty_rows' => 0, 'errors' => []];
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $result['errors'][] = 'La hoja Registro_Diario_CRM no contiene datos.';
            return $result;
        }

        $headers = $this->extractHeaders((array) reset($rows));
        $serviceId = Service::query()->value('id');

        if (! $serviceId) {
            $result['errors'][] = 'No existe un servicio predeterminado en la base de datos.';
            return $result;
        }

        $headerMap = [
            'id' => 'id',
            'service_id' => 'service_id',
            'customer_name' => 'customer_name',
            'phone' => 'phone',
            'customer_type' => 'customer_type',
            'customer_category' => 'customer_category',
            'state' => 'state',
            'city' => 'city',
            'address' => 'address',
            'contact_method' => 'contact_method',
            'status' => 'status',
            'responded' => 'responded',
            'is_recurrent' => 'is_recurrent',
            'quoted' => 'quoted',
            'closed' => 'closed',
            'has_not_coverage' => 'has_not_coverage',
            'quoted_amount' => 'quoted_amount',
            'billed_amount' => 'billed_amount',
            'payment_method' => 'payment_method',
            'invoice' => 'invoice',
            'service_date' => 'service_date',
            'quote_sent_date' => 'quote_sent_date',
            'close_date' => 'close_date',
            'payment_date' => 'payment_date',
            'follow_up_date' => 'follow_up_date',
            'service_time' => 'service_time',
            'focused_pest' => 'focused_pest',
            'notes' => 'notes',
            'status_updated_at' => 'status_updated_at',
            'status_updated_by' => 'status_updated_by',
            'fecha' => 'service_date',
            'cliente_empresa' => 'customer_name',
            'tipo_de_cliente' => 'customer_type',
            'categoria_de_cliente' => 'customer_category',
            'categoria_cliente' => 'customer_category',
            'telefono' => 'phone',
            'estado_ciudad' => 'city',
            'medio_de_contacto' => 'contact_method',
            'contesto' => 'responded',
            'recurrente' => 'is_recurrent',
            'disc' => 'notes',
            'estatus' => 'status',
            'se_cotizo' => 'quoted',
            'monto_cotizado' => 'quoted_amount',
            'se_cerro_el_servicio' => 'closed',
            'monto_facturado' => 'billed_amount',
            'fecha_cierre' => 'close_date',
            'fecha_recibi_pago_servicio' => 'payment_date',
            'plaga' => 'focused_pest',
            'distancia' => 'notes',
            'observaciones' => 'notes',
            'hora' => 'service_time',
            'domicilio' => 'address',
            'concenso' => 'has_not_coverage',
            'factura' => 'invoice',
            'metodo_pago' => 'payment_method',
        ];

        $rowNumber = 1;
        foreach (array_slice($rows, 1) as $row) {
            $rowNumber++;

            try {
                $mapped = $this->mapRowByHeader((array) $row, $headers, $headerMap);

                if ($this->isRowEmpty($mapped)) {
                    $result['empty_rows']++;
                    continue;
                }

                if ($this->isDailyTrackingSemanticallyEmpty($mapped)) {
                    $result['empty_rows']++;
                    continue;
                }

                if (empty(trim((string) ($mapped['customer_name'] ?? '')))) {
                    $mapped['customer_name'] = 'Nombre desconocido';
                }

                unset($mapped['id'], $mapped['created_at'], $mapped['updated_at']);

                $mapped['service_id'] = $serviceId;
                $mapped['customer_type'] = $this->normalizeCustomerType($mapped['customer_type'] ?? null) ?? 'comercial';
                $mapped['contact_method'] = $this->normalizeContactMethod($mapped['contact_method'] ?? null);
                $mapped['status'] = $this->normalizeStatus($mapped['status'] ?? null) ?? 'survey';
                $mapped['quoted'] = $this->normalizeQuoted($mapped['quoted'] ?? null) ?? 'pending';
                $mapped['closed'] = $this->normalizeClosed($mapped['closed'] ?? null) ?? 'pending';
                $mapped['quoted_amount'] = $this->parseAmount($mapped['quoted_amount'] ?? null);
                $mapped['billed_amount'] = $this->parseAmount($mapped['billed_amount'] ?? null);
                $mapped['payment_method'] = $this->normalizePaymentMethod($mapped['payment_method'] ?? null);
                $mapped['invoice'] = $this->normalizeInvoice($mapped['invoice'] ?? null) ?? 'not_applicable';
                $mapped['not_responded'] = $this->toBoolean($mapped['not_responded'] ?? false);
                $mapped['is_recurrent'] = $this->toBoolean($mapped['is_recurrent'] ?? false);
                $mapped['has_not_coverage'] = $this->toBoolean($mapped['has_not_coverage'] ?? false);

                if (empty($mapped['contact_method'])) {
                    $mapped['contact_method'] = 'llamada';
                }

                foreach (['service_date', 'close_date', 'payment_date', 'quote_sent_date', 'follow_up_date'] as $dateField) {
                    if (isset($mapped[$dateField]) && $mapped[$dateField] !== '') {
                        $mapped[$dateField] = $this->parseDate($mapped[$dateField]);
                    }
                }

                if (isset($mapped['service_time']) && $mapped['service_time'] !== '') {
                    $mapped['service_time'] = $this->parseTime($mapped['service_time']);
                }

                $record = DailyTracking::updateOrCreate(
                    [
                        'customer_name' => (string) $mapped['customer_name'],
                        'service_date' => $mapped['service_date'] ?? now()->toDateString(),
                    ],
                    $mapped
                );

                if ($record->wasRecentlyCreated) {
                    $result['inserted']++;
                } else {
                    $result['updated']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = "Fila {$rowNumber}: {$e->getMessage()}";
                $result['skipped']++;
                $result['skipped_rows'][] = [
                    'row_number' => $rowNumber,
                    'reason' => $e->getMessage(),
                    'row_data' => $this->sanitizeRowForLog((array) $row),
                ];
                $this->logSkippedRow('daily_tracking', $rowNumber, $e->getMessage(), (array) $row);
            }
        }

        return $result;
    }

    private function importCommercialProspectsSheet(Worksheet $sheet): array
    {
        $result = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => [], 'empty_rows' => 0, 'errors' => []];
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $result['errors'][] = 'La hoja PROSPECTOS COMERCIALES no contiene datos.';
            return $result;
        }

        $headers = $this->extractHeaders((array) reset($rows));
        $headerMap = [
            'nombre_comercial' => 'commercial_name',
            'fecha' => 'date',
            'tipo_de_comercio' => 'commerce_type',
            'cotizacion' => 'quotation_status',
            'cerro_o_motivo_de_no_cierre' => 'close_reason',
            'medio_de_contacto' => 'contact_method',
            'fecha_programada' => 'scheduled_date',
        ];
        $serviceId = Service::query()->value('id');

        $rowNumber = 1;
        foreach (array_slice($rows, 1) as $row) {
            $rowNumber++;

            try {
                $mapped = $this->mapRowByHeader((array) $row, $headers, $headerMap);

                if ($this->isRowEmpty($mapped)) {
                    $result['empty_rows']++;
                    continue;
                }

                if (empty(trim((string) ($mapped['commercial_name'] ?? '')))) {
                    $mapped['commercial_name'] = 'Nombre desconocido';
                }

                if (isset($mapped['date']) && $mapped['date'] !== '') {
                    $mapped['date'] = $this->parseDate($mapped['date']);
                }

                if (isset($mapped['scheduled_date']) && $mapped['scheduled_date'] !== '') {
                    $mapped['scheduled_date'] = $this->parseDate($mapped['scheduled_date']);
                }

                $record = CommercialProspect::updateOrCreate(
                    ['commercial_name' => (string) $mapped['commercial_name']],
                    $mapped
                );

                if ($serviceId) {
                    $this->syncProspectToDailyTracking($mapped, (int) $serviceId);
                }

                if ($record->wasRecentlyCreated) {
                    $result['inserted']++;
                } else {
                    $result['updated']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = "Fila {$rowNumber}: {$e->getMessage()}";
                $result['skipped']++;
                $result['skipped_rows'][] = [
                    'row_number' => $rowNumber,
                    'reason' => $e->getMessage(),
                    'row_data' => $this->sanitizeRowForLog((array) $row),
                ];
                $this->logSkippedRow('commercial_prospects', $rowNumber, $e->getMessage(), (array) $row);
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $mappedProspect
     */
    private function syncProspectToDailyTracking(array $mappedProspect, int $serviceId): void
    {
        $serviceDate =
            $mappedProspect['date']
            ?? $mappedProspect['scheduled_date']
            ?? now()->toDateString();

        $contactMethod = $this->normalizeContactMethod($mappedProspect['contact_method'] ?? null) ?? 'llamada';
        $status = $this->normalizeStatus($mappedProspect['quotation_status'] ?? null) ?? 'survey';

        $dailyData = [
            'service_id' => $serviceId,
            'customer_name' => (string) $mappedProspect['commercial_name'],
            'service_date' => $serviceDate,
            'customer_type' => $this->normalizeCustomerType($mappedProspect['commerce_type'] ?? null) ?? 'comercial',
            'contact_method' => $contactMethod,
            'status' => $status,
            'quoted' => 'pending',
            'closed' => 'pending',
            'not_responded' => false,
            'is_recurrent' => false,
            'has_not_coverage' => false,
            'notes' => $mappedProspect['close_reason'] ?? null,
        ];

        DailyTracking::updateOrCreate(
            [
                'customer_name' => $dailyData['customer_name'],
                'service_date' => $dailyData['service_date'],
            ],
            $dailyData
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, string>
     */
    private function extractHeaders(array $row): array
    {
        $headers = [];
        foreach ($row as $column => $value) {
            $headers[(string) $column] = $this->normalizeHeader((string) ($value ?? ''));
        }

        return $headers;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, string> $headers
     * @param array<string, string> $headerMap
     * @return array<string, mixed>
     */
    private function mapRowByHeader(array $row, array $headers, array $headerMap): array
    {
        $mapped = [];

        foreach ($row as $column => $value) {
            $header = $headers[(string) $column] ?? '';
            if ($header === '' || $value === null || $value === '') {
                continue;
            }

            foreach ($headerMap as $excelPattern => $dbField) {
                if (strpos(str_replace('_', '', $header), str_replace('_', '', $excelPattern)) !== false) {
                    $mapped[$dbField] = $value;
                    break;
                }
            }
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        return (string) preg_replace('/[^a-z0-9]+/i', '_', $header);
    }

    /**
     * Considera fila vacía cuando sólo tiene banderas/valores por defecto pero no datos de cliente.
     * @param array<string, mixed> $mappedRow
     */
    private function isDailyTrackingSemanticallyEmpty(array $mappedRow): bool
    {
        $meaningfulKeys = [
            'customer_name',
            'phone',
            'city',
            'address',
            'service_date',
            'quoted_amount',
            'billed_amount',
            'notes',
        ];

        foreach ($meaningfulKeys as $key) {
            $value = $mappedRow[$key] ?? null;
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        if (in_array($value, ['yes', 'si', 'true', '1', 'verdadero'], true)) {
            return true;
        }

        if (in_array($value, ['no', 'false', '0', 'falso', 'no aplica', 'n/a', 'na'], true)) {
            return false;
        }

        return false;
    }

    private function normalizeContactMethod(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            str_contains($raw, 'GOOGLE') => 'google',
            str_contains($raw, 'FACEBOOK'), $raw === 'FB', str_contains($raw, 'INSTAGRAM'), str_contains($raw, 'PAGINA'), str_contains($raw, 'WEB') => 'pagina',
            str_contains($raw, 'LLAMADA'), str_contains($raw, 'TELEFONO') => 'llamada',
            str_contains($raw, 'CAMBACEO') => 'cambaceo',
            str_contains($raw, 'RECOMENDACION'), str_contains($raw, 'PROSPECTO') => 'llamada',
            default => null,
        };
    }

    private function normalizeCustomerType(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        $raw = rtrim($raw, " /\\");

        return match (true) {
            str_contains($raw, 'DOMESTICO') => 'domestico',
            str_contains($raw, 'COMERCIAL') => 'comercial',
            str_contains($raw, 'INDUTRIAL'), str_contains($raw, 'INDUSTRIAL'), str_contains($raw, 'PLANTA') => 'industrial',
            default => null,
        };
    }

    private function normalizeServiceType(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        $raw = rtrim($raw, " /\\");

        return match (true) {
            str_contains($raw, 'COMERCIAL') => 'comercial',
            str_contains($raw, 'INDUTRIAL'), str_contains($raw, 'INDUSTRIAL'), str_contains($raw, 'PLANTA') => 'industrial',
            default => null,
        };
    }

    private function normalizeStatus(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            in_array($raw, ['CERRADO', 'CLOSED'], true) => 'closed',
            in_array($raw, ['CANCELLED', 'CANCELED', 'CANCELADO'], true) => 'no_requiere',
            in_array($raw, ['NO', 'N/C', 'NO REQUIERE'], true) => 'no_requiere',
            default => 'survey',
        };
    }

    /**
     * @param array<int, Worksheet> $sheets
     */
    private function findFirstDailyTrackingSheet(array $sheets): ?Worksheet
    {
        $headerAliases = [
            'customer_name',
            'cliente_empresa',
            'nombre_cliente',
            'service_date',
            'fecha',
            'fecha_servicio',
        ];

        foreach ($sheets as $sheet) {
            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) === 0) {
                continue;
            }

            $headers = $this->extractHeaders((array) reset($rows));
            $values = array_values($headers);

            $hasCustomerHeader = in_array('customer_name', $values, true) || in_array('cliente_empresa', $values, true) || in_array('nombre_cliente', $values, true);
            $hasDateHeader = in_array('service_date', $values, true) || in_array('fecha', $values, true) || in_array('fecha_servicio', $values, true);

            if ($hasCustomerHeader && $hasDateHeader) {
                return $sheet;
            }

            $matchedAliases = 0;
            foreach ($headerAliases as $alias) {
                if (in_array($alias, $values, true)) {
                    $matchedAliases++;
                }
            }

            if ($matchedAliases >= 2) {
                return $sheet;
            }
        }

        return null;
    }

    /**
     * @param array<int, Worksheet> $sheets
     */
    private function findFirstCommercialProspectsSheet(array $sheets, ?Worksheet $excludeSheet = null): ?Worksheet
    {
        $requiredHeaders = [
            'nombre_comercial',
            'cotizacion',
            'cerro_o_motivo_de_no_cierre',
            'fecha_programada',
            'medio_de_contacto',
        ];

        foreach ($sheets as $sheet) {
            if ($excludeSheet && $sheet->getTitle() === $excludeSheet->getTitle()) {
                continue;
            }

            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) === 0) {
                continue;
            }

            $headers = $this->extractHeaders((array) reset($rows));
            $values = array_values($headers);

            $matches = 0;
            foreach ($requiredHeaders as $required) {
                if (in_array($required, $values, true)) {
                    $matches++;
                }
            }

            // Exigimos al menos 2 headers clave para evitar falsos positivos.
            if ($matches >= 2 && in_array('nombre_comercial', $values, true)) {
                return $sheet;
            }
        }

        return null;
    }

    private function normalizeQuoted(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            in_array($raw, ['SI', 'SÍ', 'YES', '1', 'TRUE'], true) => 'yes',
            default => 'pending',
        };
    }

    private function normalizeClosed(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            in_array($raw, ['SI', 'SÍ', 'YES', '1', 'TRUE'], true) => 'yes',
            in_array($raw, ['NO', '0', 'FALSE'], true) => 'no',
            default => 'pending',
        };
    }

    private function normalizePaymentMethod(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            str_contains($raw, 'EFECTIVO') => 'cash',
            str_contains($raw, 'TRANSFER') => 'transfer',
            str_contains($raw, 'CHEQUE') => 'check',
            in_array($raw, ['AMBAS', 'NO CONFIRMO', 'NO CONFIRMADO', 'NO DEFINIDO'], true) => 'other',
            default => null,
        };
    }

    private function parseAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $raw = strtoupper(trim((string) $value));
        if (in_array($raw, ['NO', 'N/A', 'NA', 'PENDIENTE', 'FALSE', 'FALSO'], true)) {
            return null;
        }

        if (! preg_match('/-?\d[\d.,]*/', $raw, $match)) {
            return null;
        }

        $number = $match[0];
        $number = preg_replace('/[^\d,.-]/', '', $number) ?? '';

        if ($number === '' || $number === '-' || $number === '.' || $number === ',') {
            return null;
        }

        $hasComma = str_contains($number, ',');
        $hasDot = str_contains($number, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($number, ',');
            $lastDot = strrpos($number, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                // Formato 1.234,56
                $number = str_replace('.', '', $number);
                $number = str_replace(',', '.', $number);
            } else {
                // Formato 1,234.56
                $number = str_replace(',', '', $number);
            }
        } elseif ($hasComma) {
            // Si termina en ,dd usar coma como decimal; si no, coma como miles.
            $number = preg_match('/,\d{1,2}$/', $number)
                ? str_replace(',', '.', $number)
                : str_replace(',', '', $number);
        } elseif ($hasDot) {
            // Si no parece decimal al final, tratar punto como miles.
            if (! preg_match('/\.\d{1,2}$/', $number)) {
                $number = str_replace('.', '', $number);
            }
        }

        if (! is_numeric($number)) {
            return null;
        }

        return round((float) $number, 2);
    }

    private function normalizeInvoice(mixed $value): ?string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return match (true) {
            in_array($raw, ['SI', 'SÍ', 'YES', '1', 'TRUE'], true) => 'yes',
            in_array($raw, ['NO', '0', 'FALSE'], true) => 'no',
            in_array($raw, ['NO APLICA', 'N/A', 'NA', 'NOAPLICA'], true) => 'not_applicable',
            default => 'not_applicable',
        };
    }

    private function parseDate(mixed $dateValue): ?string
    {
        if ($dateValue === null || $dateValue === '') {
            return null;
        }

        if (is_numeric($dateValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $dateValue))->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $dateValue)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTime(mixed $timeValue): ?string
    {
        if ($timeValue === null || $timeValue === '') {
            return null;
        }

        if (is_numeric($timeValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $timeValue))->format('H:i:s');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $timeValue)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function logSkippedRow(string $sheet, int $rowNumber, string $reason, array $row): void
    {
        Log::warning('Fila omitida en importación de Excel', [
            'sheet' => $sheet,
            'row_number' => $rowNumber,
            'reason' => $reason,
            'row_data' => $this->sanitizeRowForLog($row),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, scalar|null>
     */
    private function sanitizeRowForLog(array $row): array
    {
        $sanitized = [];

        foreach ($row as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $sanitized[(string) $key] = $value;
            } else {
                $sanitized[(string) $key] = (string) json_encode($value);
            }
        }

        return $sanitized;
    }
}
