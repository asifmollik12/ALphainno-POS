<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('sku');
            }
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('category');
            }
            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 30)->default('Pcs')->after('brand');
            }
            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->after('unit');
            }
        });

        Schema::table('shop_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('shop_settings', 'warehouse_name')) {
                $table->string('warehouse_name')->nullable()->after('company_name');
            }
            if (! Schema::hasColumn('shop_settings', 'default_tax_rate')) {
                $table->decimal('default_tax_rate', 5, 2)->default(0)->after('currency');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'contact_person' => 'string',
                'mobile' => 'string',
                'website' => 'string',
                'tax_number' => 'string',
                'billing_country' => 'string',
                'billing_city' => 'string',
                'shipping_address' => 'text',
                'shipping_country' => 'string',
                'shipping_city' => 'string',
            ] as $col => $type) {
                if (! Schema::hasColumn('customers', $col)) {
                    if ($type === 'text') {
                        $table->text($col)->nullable();
                    } else {
                        $table->string($col)->nullable();
                    }
                }
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            $cols = [
                'subtotal' => fn () => $table->decimal('subtotal', 12, 2)->default(0)->after('total'),
                'tax_amount' => fn () => $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal'),
                'discount_amount' => fn () => $table->decimal('discount_amount', 12, 2)->default(0)->after('tax_amount'),
                'payment_method' => fn () => $table->string('payment_method', 30)->default('cash')->after('payment_status'),
                'warehouse' => fn () => $table->string('warehouse')->nullable()->after('payment_method'),
                'delivery_status' => fn () => $table->string('delivery_status', 30)->default('delivered')->after('warehouse'),
                'payment_reference' => fn () => $table->string('payment_reference')->nullable()->after('delivery_status'),
            ];
            foreach ($cols as $col => $callback) {
                if (! Schema::hasColumn('sales', $col)) {
                    $callback();
                }
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'unit')) {
                $table->string('unit', 30)->default('Pcs')->after('product_name');
            }
            if (! Schema::hasColumn('sale_items', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('unit_price');
            }
            if (! Schema::hasColumn('sale_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('discount');
            }
            if (! Schema::hasColumn('sale_items', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('sale_items', 'unit') ? 'unit' : null,
                Schema::hasColumn('sale_items', 'discount') ? 'discount' : null,
                Schema::hasColumn('sale_items', 'tax_rate') ? 'tax_rate' : null,
                Schema::hasColumn('sale_items', 'tax_amount') ? 'tax_amount' : null,
            ]));
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('sales', 'subtotal') ? 'subtotal' : null,
                Schema::hasColumn('sales', 'tax_amount') ? 'tax_amount' : null,
                Schema::hasColumn('sales', 'discount_amount') ? 'discount_amount' : null,
                Schema::hasColumn('sales', 'payment_method') ? 'payment_method' : null,
                Schema::hasColumn('sales', 'warehouse') ? 'warehouse' : null,
                Schema::hasColumn('sales', 'delivery_status') ? 'delivery_status' : null,
                Schema::hasColumn('sales', 'payment_reference') ? 'payment_reference' : null,
            ]));
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('customers', 'contact_person') ? 'contact_person' : null,
                Schema::hasColumn('customers', 'mobile') ? 'mobile' : null,
                Schema::hasColumn('customers', 'website') ? 'website' : null,
                Schema::hasColumn('customers', 'tax_number') ? 'tax_number' : null,
                Schema::hasColumn('customers', 'billing_country') ? 'billing_country' : null,
                Schema::hasColumn('customers', 'billing_city') ? 'billing_city' : null,
                Schema::hasColumn('customers', 'shipping_address') ? 'shipping_address' : null,
                Schema::hasColumn('customers', 'shipping_country') ? 'shipping_country' : null,
                Schema::hasColumn('customers', 'shipping_city') ? 'shipping_city' : null,
            ]));
        });

        Schema::table('shop_settings', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('shop_settings', 'warehouse_name') ? 'warehouse_name' : null,
                Schema::hasColumn('shop_settings', 'default_tax_rate') ? 'default_tax_rate' : null,
            ]));
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('products', 'category') ? 'category' : null,
                Schema::hasColumn('products', 'brand') ? 'brand' : null,
                Schema::hasColumn('products', 'unit') ? 'unit' : null,
                Schema::hasColumn('products', 'barcode') ? 'barcode' : null,
            ]));
        });
    }
};
