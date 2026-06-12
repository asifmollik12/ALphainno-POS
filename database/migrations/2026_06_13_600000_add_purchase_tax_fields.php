<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'tax_amount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('total');
            });
        }

        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_items', 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_cost');
                }
                if (! Schema::hasColumn('purchase_items', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_rate');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'tax_amount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('tax_amount');
            });
        }

        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                foreach (['tax_rate', 'tax_amount'] as $col) {
                    if (Schema::hasColumn('purchase_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
