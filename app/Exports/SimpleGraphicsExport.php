<?php

namespace App\Exports;

class SimpleGraphicsExport
{
    protected $data;
    protected $graphType;
    protected $headers;
    protected $title;

    public function __construct($data, string $graphType, array $headers, string $title = 'Reporte')
    {
        $this->data = $data;
        $this->graphType = $graphType;
        $this->headers = $headers;
        $this->title = $title;
    }

    public function getRows(): array
    {
        $rows = [];
        $index = 1;

        // Convertir data a array si es objeto
        $dataArray = (array) $this->data;
        
        // Asegurarnos de que tenemos el array 'detections'
        $detections = $dataArray['detections'] ?? [];

        foreach ($detections as $detection) {
            // Convertir detección a array si es objeto
            $detectionArray = (array) $detection;
            
            $row = [
                '#' => $index,
                'Servicio' => $detectionArray['service'] ?? '',
                'Área' => $detectionArray['area_name'] ?? '',
                'Dispositivo' => $detectionArray['device_name'] ?? '',
                'Versión' => $this->formatVersion($detectionArray['versions'] ?? null),
            ];

            if ($this->graphType == 'cptr') {
                $pestDetections = (array) ($detectionArray['pest_total_detections'] ?? []);
                foreach ($this->headers as $header) {
                    $row[$header] = $pestDetections[$header] ?? 0;
                }
            } elseif ($this->graphType == 'cnsm') {
                $weeklyConsumption = (array) ($detectionArray['weekly_consumption'] ?? []);
                if (!empty($weeklyConsumption)) {
                    foreach ($this->headers as $header) {
                        $row[$header] = $weeklyConsumption[$header] ?? 0;
                    }
                } else {
                    foreach ($this->headers as $header) {
                        $row[$header] = 0;
                    }
                    if (isset($detectionArray['consumption_value'])) {
                        $firstHeader = $this->headers[0] ?? 'Consumo';
                        $row[$firstHeader] = $detectionArray['consumption_value'];
                    }
                }
            }

            $rows[] = $row;
            $index++;
        }

        // Agregar fila de totales
        if (!empty($detections)) {
            $totalRow = [
                '#' => 'TOTAL GENERAL',
                'Servicio' => '',
                'Área' => '',
                'Dispositivo' => '',
                'Versión' => '',
            ];

            if ($this->graphType == 'cptr') {
                $grandTotals = (array) ($dataArray['grand_totals'] ?? []);
                foreach ($this->headers as $header) {
                    $totalRow[$header] = $grandTotals[$header] ?? 0;
                }
            } elseif ($this->graphType == 'cnsm') {
                $grandTotalsWeekly = (array) ($dataArray['grand_totals_weekly'] ?? []);
                if (!empty($grandTotalsWeekly)) {
                    foreach ($this->headers as $header) {
                        $totalRow[$header] = $grandTotalsWeekly[$header] ?? 0;
                    }
                } else {
                    foreach ($this->headers as $header) {
                        $totalRow[$header] = 0;
                    }
                    if (isset($dataArray['grand_total_consumption'])) {
                        $firstHeader = $this->headers[0] ?? 'Consumo';
                        $totalRow[$firstHeader] = $dataArray['grand_total_consumption'];
                    }
                }
            }

            $rows[] = $totalRow;
        }

        return $rows;
    }

    private function formatVersion($versions): string
    {
        if (is_array($versions)) {
            return implode(', ', $versions);
        }
        
        if (is_string($versions)) {
            // Intentar decodificar JSON si es una cadena JSON
            $decoded = json_decode($versions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(', ', $decoded);
            }
            return $versions;
        }
        
        if (is_object($versions)) {
            return implode(', ', (array) $versions);
        }
        
        return (string) $versions;
    }
}