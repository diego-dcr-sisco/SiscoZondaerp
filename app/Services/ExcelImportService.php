<?php

namespace App\Services;

use App\Imports\CommercialProspectsImport;
use App\Imports\DailyTrackingImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
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
                    'skipped' => 0,
                    'errors' => [],
                ],
                'commercial_prospects' => [
                    'inserted' => 0,
                    'skipped' => 0,
                    'errors' => [],
                ],
            ],
            'total_records' => 0,
            'import_time' => null,
        ];

        $startTime = Carbon::now();

        try {
            DB::beginTransaction();

            // Importar hoja 1: Registro_Diario_CRM
            Log::info('Iniciando importación de Registro_Diario_CRM');
            $dailyTrackingImport = new DailyTrackingImport();

            try {
                Excel::import($dailyTrackingImport, $filePath, null, \Maatwebsite\Excel\Excel::XLSX);
                $result['data']['daily_tracking']['inserted'] = $dailyTrackingImport->getInsertedCount();
                $result['data']['daily_tracking']['skipped'] = $dailyTrackingImport->getSkippedCount();
                $result['data']['daily_tracking']['errors'] = $dailyTrackingImport->getErrors();

                Log::info('Hoja Registro_Diario_CRM importada', [
                    'inserted' => $result['data']['daily_tracking']['inserted'],
                    'skipped' => $result['data']['daily_tracking']['skipped'],
                ]);
            } catch (\Exception $e) {
                Log::error('Error importando Registro_Diario_CRM: ' . $e->getMessage());
                $result['data']['daily_tracking']['errors'][] = 'Error general: ' . $e->getMessage();
            }

            // Importar hoja 2: PROSPECTOS COMERCIALES
            Log::info('Iniciando importación de PROSPECTOS COMERCIALES');
            $commercialProspectsImport = new CommercialProspectsImport();

            try {
                Excel::import($commercialProspectsImport, $filePath, null, \Maatwebsite\Excel\Excel::XLSX);
                $result['data']['commercial_prospects']['inserted'] = $commercialProspectsImport->getInsertedCount();
                $result['data']['commercial_prospects']['skipped'] = $commercialProspectsImport->getSkippedCount();
                $result['data']['commercial_prospects']['errors'] = $commercialProspectsImport->getErrors();

                Log::info('Hoja PROSPECTOS COMERCIALES importada', [
                    'inserted' => $result['data']['commercial_prospects']['inserted'],
                    'skipped' => $result['data']['commercial_prospects']['skipped'],
                ]);
            } catch (\Exception $e) {
                Log::error('Error importando PROSPECTOS COMERCIALES: ' . $e->getMessage());
                $result['data']['commercial_prospects']['errors'][] = 'Error general: ' . $e->getMessage();
            }

            DB::commit();

            $totalRecords = $result['data']['daily_tracking']['inserted'] + $result['data']['commercial_prospects']['inserted'];
            $totalErrors = count($result['data']['daily_tracking']['errors']) + count($result['data']['commercial_prospects']['errors']);

            if ($totalRecords > 0) {
                $result['success'] = true;
                $result['message'] = "Importación completada: {$totalRecords} registros insertados";

                if ($totalErrors > 0) {
                    $result['message'] .= " ({$totalErrors} errores)";
                }
            } else {
                $result['message'] = 'No se insertaron registros. Revisa los errores para más detalles.';
            }

            $result['total_records'] = $totalRecords;
            $result['import_time'] = Carbon::now()->diffInSeconds($startTime);

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
}
