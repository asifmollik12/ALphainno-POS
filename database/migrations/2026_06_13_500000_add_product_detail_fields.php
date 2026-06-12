<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'uom_value')) {
                $table->decimal('uom_value', 12, 2)->default(0)->after('unit');
            }
            if (! Schema::hasColumn('products', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('products', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('discount_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['uom_value', 'discount_rate', 'tax_rate'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
