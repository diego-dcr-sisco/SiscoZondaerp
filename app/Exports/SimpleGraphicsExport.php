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

        $headers = $this->getHeaders($this->data);

        foreach ($this->data['detections'] as $index => $detection) {
            $row_data = [
                $detection['device_name'] ?? '',
                $detection['service'] ?? '',
                $detection['area_name'] ?? '',
                $this->formatVersion($detection['versions'] ?? ''),
            ];

            $array_count = [];
            $count = 0;
            
            foreach ($this->data['headers'] as $header_key) {
                if ($this->graphType == 'cnsm') {
                    // Para gráfico de consumo
                    $value = $detection['weekly_consumption'][$header_key] ?? 0;
                    $count += $value;
                    array_push($array_count, $value);
                } else {
                    // Para gráfico de plagas - usa la nueva estructura 'pests'
                    $value = $detection['pests'][$header_key] ?? 0;
                    array_push($array_count, $value);
                }
            }
            
            $rows[] = array_merge($row_data, $array_count);
            
            if ($this->graphType == 'cnsm') {
                // Solo agregar columna de total para consumo
                $rows[$index][] = $count;
            }
        }

        $array_count = [];

        // Usar la clave correcta según el tipo de gráfico
        $grandTotalsKey = $this->graphType == 'cnsm' ? 'grand_totals_weekly' : 'grand_totals';

        if (isset($this->data[$grandTotalsKey])) {
            foreach ($this->data['headers'] as $header_key) {
                // Para consumo, los totales son arrays asociativos
                if ($this->graphType == 'cnsm' && is_array($this->data[$grandTotalsKey])) {
                    $value = $this->data[$grandTotalsKey][$header_key] ?? 0;
                } 
                // Para plagas, los totales también son arrays asociativos
                elseif ($this->graphType == 'cptr' && is_array($this->data[$grandTotalsKey])) {
                    $value = $this->data[$grandTotalsKey][$header_key] ?? 0;
                } else {
                    $value = 0;
                }
                array_push($array_count, $value);
            }
        } else {
            // Si no existe, llenar con ceros
            foreach ($this->data['headers'] as $header_key) {
                array_push($array_count, 0);
            }
        }

        // Agregar fila de totales
        $total_label = $this->graphType == 'cnsm' ? 'Total general' : 'Total por plaga';
        $rows[] = array_merge(['', '', '', '', $total_label], $array_count);

        // Si es consumo, agregar también el total general en la última columna
        if ($this->graphType == 'cnsm' && isset($this->data['grand_total_consumption'])) {
            $rows[count($rows) - 1][] = $this->data['grand_total_consumption'];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function getHeaders($data): array
    {
        $headers = ['#', 'Servicio', 'Área', 'Dispositivo', 'Versión'];
        
        // Verificar si existen headers en los datos
        if (isset($data['headers']) && count($data['headers']) > 0) {
            $headers = array_merge($headers, $data['headers']);
        }

        if ($this->graphType == 'cnsm') {
            $headers = array_merge($headers, ['Total por dispositivo']);
        }
        
        return $headers;
    }

    private function formatVersion($versions): string
    {
        if (empty($versions)) {
            return '';
        }

        if (is_array($versions)) {
            return implode(', ', array_unique($versions));
        }

        if (is_string($versions)) {
            $decoded = json_decode($versions, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(', ', array_unique($decoded));
            }
            return $versions;
        }

        if (is_object($versions)) {
            return implode(', ', array_unique((array) $versions));
        }

        return (string) $versions;
    }
}