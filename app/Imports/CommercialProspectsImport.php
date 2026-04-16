<?php

namespace App\Imports;

use App\Models\CommercialProspect;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommercialProspectsImport implements ToCollection, WithHeadingRow
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
            'nombre_comercial' => 'commercial_name',
            'fecha' => 'date',
            'tipo_de_comercio' => 'commerce_type',
            'cotizacion' => 'quotation_status',
            'cerro_o_motivo_de_no_cierre' => 'close_reason',
            'medio_de_contacto' => 'contact_method',
            'fecha_programada' => 'scheduled_date',
        ];
    }

    /**
     * Normaliza headers del Excel para búsqueda de concordancia
     */
    private function normalizeHeader(string $header): string
    {
        return strtolower(
            trim(
                str_replace(
                    ['/', '?', '¿', '!', '¡', ' ', '-', 'o'],
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
                'excel' => 'La hoja "PROSPECTOS COMERCIALES" no contiene datos.',
            ]);
        }

        foreach ($rows as $rowIndex => $row) {
            try {
                $this->processRow($row, $rowIndex + 2);
            } catch (\Exception $e) {
                $this->errors[] = "Fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Procesa una fila individual
     */
    private function processRow($rowData, int $rowNumber): void
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
        if (empty($mappedData['commercial_name'])) {
            throw new \Exception('Nombre Comercial vacío');
        }

        // Validar fechas
        if (isset($mappedData['date']) && !$this->isValidDate($mappedData['date'])) {
            throw new \Exception('Fecha inválida');
        }

        if (isset($mappedData['scheduled_date']) && !$this->isValidDate($mappedData['scheduled_date'])) {
            throw new \Exception('Fecha programada inválida');
        }

        // Convertir fechas
        if (isset($mappedData['date']) && $mappedData['date']) {
            $mappedData['date'] = $this->parseDate($mappedData['date']);
        }

        if (isset($mappedData['scheduled_date']) && $mappedData['scheduled_date']) {
            $mappedData['scheduled_date'] = $this->parseDate($mappedData['scheduled_date']);
        }

        // Crear o actualizar registro
        CommercialProspect::updateOrCreate(
            [
                'commercial_name' => $mappedData['commercial_name'],
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
                // Búsqueda flexible de headers
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
     * Parsea una fecha
     */
    private function parseDate($dateValue)
    {
        if (is_numeric($dateValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->toDateString();
            } catch (\Exception) {
                return null;
            }
        }

        try {
            return Carbon::parse($dateValue)->toDateString();
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
