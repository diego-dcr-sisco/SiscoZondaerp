<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_service', function (Blueprint $table) {
            $table->date('generation_end_date')->nullable()->after('generation_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('contract_service', function (Blueprint $table) {
            $table->dropColumn('generation_end_date');
        });
    }
};
