<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiado a 'product_input' en singular
        Schema::table('product_input', function (Blueprint $table) {
            $table->json('pest_ids')->nullable()->after('pest_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_input', function (Blueprint $table) {
            $table->dropColumn('pest_ids');
        });
    }
};
