<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'refunded_amount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('refunded_amount', 12, 2)->default(0)->after('returned_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'refunded_amount')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('refunded_amount');
            });
        }
    }
};
