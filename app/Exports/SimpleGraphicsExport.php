<?php

namespace App\Exports;

class SimpleGraphicsExport
{
    protected $data;
    protected $graphType;

    public function __construct($data, string $graphType)
    {
        $this->data = $data;
        $this->graphType = $graphType;
    }

    public function getRows(): array
    {
        $rows = [];
        $index = 1;

        //dd($this->data);
        $headers = $this->getHeaders($this->data);

        foreach ($this->data['detections'] as $index => $detection) {
            $row_data = [
                ($index + 1),
                $detection['service'],
                $detection['area_name'],
                $detection['device_name'],
                $this->formatVersion($detection['versions']),
            ];

            $array_count = [];
            $count = 0;
            foreach ($this->data['headers'] as $header_key) {
                if ($this->graphType == 'cnsm') {
                    $count += $detection['weekly_consumption'][$header_key];
                    array_push($array_count, $detection['weekly_consumption'][$header_key]);
                } else {
                    array_push($array_count, $detection['pest_total_detections'][$header_key]);
                }
            }
            $rows[] = array_merge($row_data, $array_count);
            if ($this->graphType == 'cnsm') {
                array_push($rows[$index], $count);
            }
        }

        $array_count = [];

        foreach ($this->data['headers'] as $header_key) {
            array_push($array_count, $this->data['grand_totals_weekly'][$header_key]);
        }

        $rows[] = array_merge(['', '', '', '', 'Totales'], $array_count);

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function getHeaders($data): array
    {
        $headers = ['#', 'Servicio', 'Area', 'Dispositivo', 'Version'];
        if (count($data['headers']) > 0) {
            $headers = array_merge($headers, $data['headers']);
        }

        if($this->graphType == 'cnsm') {
            $headers = array_merge($headers, ['Total p/ dispositivo']);
        }
        return $headers;
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