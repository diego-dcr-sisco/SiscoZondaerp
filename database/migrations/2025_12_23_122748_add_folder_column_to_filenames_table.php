<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la tabla existe y si la columna no existe ya
        if (Schema::hasTable('filenames') && !Schema::hasColumn('filenames', 'folder')) {
            Schema::table('filenames', function (Blueprint $table) {
                $table->string('folder')
                    ->nullable()
                    ->after('id')
                    ->comment('Carpeta donde se almacena el archivo');
                
                // Opcional: agregar índice
                $table->index('folder');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('filenames', 'folder')) {
            Schema::table('filenames', function (Blueprint $table) {
                $table->dropIndex(['folder']); // Si creaste un índice
                $table->dropColumn('folder');
            });
        }
    }
};