<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupAndRestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-and-restore-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Respaldar la base de datos
        $backupPath = storage_path('app/backups') . '/' . date('Y-m-d_H-i-s') . '.sql';
        $this->info('Backing up database...');
        $this->call('backup:run');

        $this->info('Restaurando base de datos');

        $backupSql = file_get_contents($backupPath);
        $tablesToRestore = [
            'user_file',
            'user', 
            'payment_method', 
            'frequency_type', 
            'sku_file', 
            'database_log', 
            'user_contract', 
            'pest_catalog', 
            'customer_contract',
            'service',
            'app_method_service',
            'technician',
            'administrative',
            'customer_file',
            'customer',
            'floorplans',
            'customer_restrictions',
            'floortype',
            'customer_reference',
            'contract_service',
            'lead_customer',
            'supplier',
            'economic_data_product',
            'product_catalog',
            'application_areas',
            'control_point',
            'device',
            'order',
            'order_technician',
            'order_service',
            'pest_order',
            'question',
            'product_file',
            'control_point_question',
            'product_pest',
            'frequency',
            'control_point_app_methods',
            'password_reset_tokens',
            'failed_jobs',
            'permission',
            'order_product'
        ];

        foreach ($tablesToRestore as $tableName) {
            $this->restoreTable($backupSql, $tableName);
        }

        // 2. Restaurar la base de datos
        $this->info('Restoring database...');
        DB::unprepared($backupSql);
        $this->info('Database restored successfully!');
    }
    protected function restoreTable($backupPath, $tableName)
    {
        $this->info("Restoring table: $tableName");
        $sql = file_get_contents($backupPath);

        // Encuentra la sección SQL correspondiente a la estructura de la tabla
        preg_match("/-- Table structure for table `{$tableName}`(.+?)-- Table structure/s", $sql, $structureMatches);

        if (isset($structureMatches[1])) {
            // Restaura la estructura de la tabla
            DB::unprepared($structureMatches[1]);

            // Encuentra la sección SQL correspondiente a los datos de la tabla
            preg_match("/-- Dumping data for table `{$tableName}`(.+?)(-- Table structure|$)/s", $sql, $dataMatches);

            if (isset($dataMatches[1])) {
                // Restaura los datos de la tabla
                DB::unprepared($dataMatches[1]);
            }
        }
    }
}
