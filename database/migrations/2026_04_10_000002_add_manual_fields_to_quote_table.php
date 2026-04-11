<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quote', function (Blueprint $table) {
            $table->string('title')->default('Cotizacion de Servicios')->after('model_type');
            $table->string('quote_no')->nullable()->after('title');
            $table->date('issued_date')->nullable()->after('quote_no');
            $table->string('currency', 10)->default('MXN')->after('issued_date');
            $table->decimal('tax_percent', 8, 2)->default(16)->after('currency');
            $table->text('payment_terms')->nullable()->after('comments');
            $table->text('delivery_time')->nullable()->after('payment_terms');
            $table->text('conditions')->nullable()->after('delivery_time');
            $table->text('notes')->nullable()->after('conditions');

            $table->index('quote_no');
        });
    }

    public function down(): void
    {
        Schema::table('quote', function (Blueprint $table) {
            $table->dropIndex(['quote_no']);
            $table->dropColumn([
                'title',
                'quote_no',
                'issued_date',
                'currency',
                'tax_percent',
                'payment_terms',
                'delivery_time',
                'conditions',
                'notes',
            ]);
        });
    }
};