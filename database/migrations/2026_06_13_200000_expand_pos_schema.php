<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('products', 'min_stock')) {
                $table->unsignedInteger('min_stock')->default(5)->after('stock');
            }
        });

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->decimal('balance_due', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->decimal('balance_due', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('sales', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            }
            if (! Schema::hasColumn('sales', 'returned_amount')) {
                $table->decimal('returned_amount', 12, 2)->default(0)->after('due_amount');
            }
            if (! Schema::hasColumn('sales', 'payment_status')) {
                $table->string('payment_status', 20)->default('paid')->after('returned_amount');
            }
            if (! Schema::hasColumn('sales', 'sale_date')) {
                $table->date('sale_date')->nullable()->after('payment_status');
            }
        });

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('total', 12, 2);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('due_amount', 12, 2)->default(0);
                $table->decimal('returned_amount', 12, 2)->default(0);
                $table->string('payment_status', 20)->default('due');
                $table->date('purchase_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_cost', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('status', 20)->default('pending');
                $table->date('order_date');
                $table->date('expected_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_cost', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('total', 12, 2);
                $table->date('return_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_cost', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('reference')->unique();
                $table->decimal('total', 12, 2);
                $table->date('return_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 20)->nullable();
                $table->string('type', 30);
                $table->decimal('opening_balance', 12, 2)->default(0);
                $table->decimal('current_balance', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('account_id')->constrained()->cascadeOnDelete();
                $table->string('type', 10);
                $table->decimal('amount', 12, 2);
                $table->string('reference')->nullable();
                $table->string('description')->nullable();
                $table->date('transaction_date');
                $table->string('related_type')->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shop_settings')) {
            Schema::create('shop_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('company_name')->nullable();
                $table->string('currency', 10)->default('৳');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'sale_date')) {
            DB::table('sales')->whereNull('sale_date')->update([
                'sale_date' => DB::raw('DATE(created_at)'),
            ]);
            if (Schema::hasColumn('sales', 'paid_amount')) {
                DB::table('sales')->where('paid_amount', 0)->update([
                    'paid_amount' => DB::raw('total'),
                    'payment_status' => 'paid',
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }
            $table->dropColumn(array_filter([
                Schema::hasColumn('sales', 'paid_amount') ? 'paid_amount' : null,
                Schema::hasColumn('sales', 'due_amount') ? 'due_amount' : null,
                Schema::hasColumn('sales', 'returned_amount') ? 'returned_amount' : null,
                Schema::hasColumn('sales', 'payment_status') ? 'payment_status' : null,
                Schema::hasColumn('sales', 'sale_date') ? 'sale_date' : null,
            ]));
        });

        Schema::table('products', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('products', 'cost_price') ? 'cost_price' : null,
                Schema::hasColumn('products', 'min_stock') ? 'min_stock' : null,
            ]);
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
