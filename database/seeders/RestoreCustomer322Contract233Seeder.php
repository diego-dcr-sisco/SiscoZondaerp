<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RestoreCustomer322Contract233Seeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/restore_customer_322_contract_233.sql');

        if (! file_exists($path)) {
            throw new RuntimeException("Restore SQL file not found: {$path}");
        }

        foreach ($this->splitSqlStatements(file_get_contents($path)) as $statement) {
            DB::statement($statement);
        }
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $statement = '';
        $quote = null;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $statement .= $char;

            if ($quote !== null) {
                if ($char === '\\') {
                    $index++;

                    if ($index < $length) {
                        $statement .= $sql[$index];
                    }

                    continue;
                }

                if ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                continue;
            }

            if ($char !== ';') {
                continue;
            }

            $trimmedStatement = trim($statement);

            if ($trimmedStatement !== '') {
                $statements[] = $trimmedStatement;
            }

            $statement = '';
        }

        $trimmedStatement = trim($statement);

        if ($trimmedStatement !== '') {
            $statements[] = $trimmedStatement;
        }

        return $statements;
    }
}
