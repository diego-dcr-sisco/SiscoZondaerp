<?php

namespace App\Imports;

use App\Models\DailyTracking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DailyTrackingImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $insertedCount = 0;
    private $skippedCount = 0;

    /**
     * Mapeo de headers exactos del Excel a campos de base de datos
     */
    private function getHeaderMap(): array
    {
        return [
            'fecha' => 'service_date',
            'cliente_empresa' => 'customer_name',
            'telefono' => 'phone',
            'estado_ciudad' => 'city',
            'medio_de_contacto' => 'contact_method',
            'contesto' => 'responded',
            'disc' => 'notes',
            'estatus' => 'status',
            'se_cotizo' => 'quoted',
            'monto_cotizado' => 'quoted_amount',
            'se_cerro_el_servicio' => 'closed',
            'monto_facturado' => 'billed_amount',
            'fecha_cierre' => 'close_date',
            'fecha_recibi_pago_servicio' => 'payment_date',
            'plaga' => 'notes',
            'distancia' => 'notes',
            'observaciones' => 'notes',
            'hora' => 'service_time',
            'domicilio' => 'address',
            'concenso' => 'has_coverage',
            'tipo_de_servicio' => 'service_type',
            'factura' => 'invoice',
            'metodo_pago' => 'payment_method',
        ];
    }

    /**
     * Normaliza headers del Excel para buscar concordancia
     */
    private function normalizeHeader(string $header): string
    {
        return strtolower(
            trim(
                str_replace(
                    ['/', '?', '¿', '!', '¡', ' ', '-'],
                    '_',
                    $header
                )
            )
        );
    }

    /**
     * Procesa la colección de datos del Excel
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'excel' => 'La hoja "Registro_Diario_CRM" no contiene datos.',
            ]);
        }

        $defaultService = Service::first();
        if (!$defaultService) {
            throw ValidationException::withMessages([
                'excel' => 'No existe un servicio predeterminado en la base de datos. Cree uno primero.',
            ]);
        }

        foreach ($rows as $rowIndex => $row) {
            try {
                $this->processRow($row, $rowIndex + 2, $defaultService->id); // +2 por header + 1-indexing
            } catch (\Exception $e) {
                $this->errors[] = "Fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Procesa una fila individual
     */
    private function processRow($rowData, int $rowNumber, int $serviceId): void
    {
        // Filtrar datos nulos o vacíos
        $cleanedRow = array_filter($rowData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($cleanedRow)) {
            $this->skippedCount++;
            return; // Saltar filas vacías
        }

        $mappedData = $this->mapRowData($cleanedRow);

        // Validaciones básicas
        if (empty($mappedData['customer_name'])) {
            throw new \Exception('Cliente/Empresa vacío');
        }

        // Validar fechas
        if (isset($mappedData['service_date']) && !$this->isValidDate($mappedData['service_date'])) {
            throw new \Exception('Fecha de servicio inválida');
        }

        if (isset($mappedData['close_date']) && !$this->isValidDate($mappedData['close_date'])) {
            throw new \Exception('Fecha de cierre inválida');
        }

        if (isset($mappedData['payment_date']) && !$this->isValidDate($mappedData['payment_date'])) {
            throw new \Exception('Fecha de pago inválida');
        }

        // Validar números
        if (isset($mappedData['quoted_amount']) && !$this->isValidNumber($mappedData['quoted_amount'])) {
            throw new \Exception('Monto cotizado inválido');
        }

        if (isset($mappedData['billed_amount']) && !$this->isValidNumber($mappedData['billed_amount'])) {
            throw new \Exception('Monto facturado inválido');
        }

        // Asignar valores por defecto
        $mappedData['service_id'] = $serviceId;
        $mappedData['customer_type'] = $mappedData['customer_type'] ?? 'comercial';
        $mappedData['status'] = $mappedData['status'] ?? 'survey';

        // Convertir booleanos
        if (isset($mappedData['responded'])) {
            $mappedData['responded'] = $this->toBoolean($mappedData['responded']);
        } else {
            $mappedData['responded'] = false;
        }

        if (isset($mappedData['has_coverage'])) {
            $mappedData['has_coverage'] = $this->toBoolean($mappedData['has_coverage']);
        } else {
            $mappedData['has_coverage'] = false;
        }

        // Convertir fechas
        foreach (['service_date', 'close_date', 'payment_date', 'quote_sent_date', 'follow_up_date'] as $dateField) {
            if (isset($mappedData[$dateField]) && $mappedData[$dateField]) {
                $mappedData[$dateField] = $this->parseDate($mappedData[$dateField]);
            }
        }

        // Convertir hora
        if (isset($mappedData['service_time']) && $mappedData['service_time']) {
            $mappedData['service_time'] = $this->parseTime($mappedData['service_time']);
        }

        // Crear o actualizar registro
        DailyTracking::updateOrCreate(
            [
                'customer_name' => $mappedData['customer_name'],
                'service_date' => $mappedData['service_date'] ?? now()->toDateString(),
            ],
            $mappedData
        );

        $this->insertedCount++;
    }

    /**
     * Mapea datos de fila a estructura de base de datos
     */
    private function mapRowData($rowData): array
    {
        $mapped = [];
        $headerMap = $this->getHeaderMap();

        foreach ($rowData as $header => $value) {
            $normalizedHeader = $this->normalizeHeader((string) $header);

            foreach ($headerMap as $excelPattern => $dbField) {
                if (strpos($normalizedHeader, str_replace('_', '', $excelPattern)) !== false) {
                    $mapped[$dbField] = $value;
                    break;
                }
            }
        }

        return $mapped;
    }

    /**
     * Valida si es una fecha válida
     */
    private function isValidDate($dateString): bool
    {
        if (is_numeric($dateString)) {
            // Excel serial date
            return true;
        }

        try {
            Carbon::parse($dateString);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Convierte y valida números
     */
    private function isValidNumber($numberString): bool
    {
        if (is_numeric($numberString)) {
            return true;
        }

        $cleaned = str_replace([',', '.'], '', (string) $numberString);
        return is_numeric($cleaned);
    }

    /**
     * Convierte valor a booleano
     */
    private function toBoolean($value): bool
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['yes', 'sí', 'si', '1', 'true', 'verdadero'], true);
    }

    /**
     * Parsea una fecha
     */
    private function parseDate($dateValue)
    {
        if (is_numeric($dateValue)) {
            // Excel serial date
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->toDateString();
        }

        try {
            return Carbon::parse($dateValue)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parsea una hora
     */
    private function parseTime($timeValue)
    {
        if (is_numeric($timeValue)) {
            // Excel decimal time
            $seconds = $timeValue * 86400;
            return gmdate('H:i:s', $seconds);
        }

        try {
            return Carbon::parse($timeValue)->format('H:i:s');
        } catch (\Exception) {
            return null;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getInsertedCount(): int
    {
        return $this->insertedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
