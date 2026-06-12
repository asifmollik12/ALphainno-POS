<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopSetting;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemoDataService
{
    public function __construct(private ShopBootstrapService $bootstrap) {}

    public function seed(User $user, bool $fresh = false): array
    {
        $this->bootstrap->ensureDefaults($user);

        if ($fresh) {
            $this->clearUserData($user);
        } elseif ($user->products()->exists()) {
            return ['skipped' => true, 'message' => 'User already has products. Use --fresh to replace demo data.'];
        }

        return DB::transaction(function () use ($user) {
            $this->seedShopSettings($user);
            $suppliers = $this->seedSuppliers($user);
            $customers = $this->seedCustomers($user);
            $products = $this->seedProducts($user);
            $purchases = $this->seedPurchases($user, $suppliers, $products);
            $sales = $this->seedSales($user, $customers, $products);
            $this->seedPurchaseOrders($user, $suppliers, $products);

            return [
                'skipped' => false,
                'products' => count($products),
                'customers' => count($customers),
                'suppliers' => count($suppliers),
                'purchases' => count($purchases),
                'sales' => count($sales),
            ];
        });
    }

    /** Add demo shortage products and mark some existing stock as low. */
    public function seedShortage(User $user): array
    {
        $this->bootstrap->ensureDefaults($user);

        $catalog = [
            ['name' => 'Marker Black', 'sku' => 'SHR-001', 'category' => 'Stationery', 'brand' => 'Matador', 'barcode' => '8801002001001', 'cost' => 25, 'price' => 40, 'stock' => 0, 'min' => 10],
            ['name' => 'Field Notebook A5', 'sku' => 'SHR-002', 'category' => 'Stationery', 'brand' => 'Olympic', 'barcode' => '8801002001002', 'cost' => 45, 'price' => 65, 'stock' => 0, 'min' => 8],
            ['name' => 'Whiteboard Marker Blue', 'sku' => 'SHR-003', 'category' => 'Stationery', 'brand' => 'Deli', 'barcode' => '8801002001003', 'cost' => 35, 'price' => 55, 'stock' => 0, 'min' => 6],
            ['name' => 'Glue Stick 21g', 'sku' => 'SHR-004', 'category' => 'Stationery', 'brand' => 'Faber-Castell', 'barcode' => '8801002001004', 'cost' => 30, 'price' => 50, 'stock' => 0, 'min' => 12],
            ['name' => 'Eraser Large', 'sku' => 'SHR-005', 'category' => 'Stationery', 'brand' => 'Dollar', 'barcode' => '8801002001005', 'cost' => 8, 'price' => 15, 'stock' => 0, 'min' => 20],
            ['name' => 'Highlighter Yellow', 'sku' => 'SHR-006', 'category' => 'Stationery', 'brand' => 'Stabilo', 'barcode' => '8801002001006', 'cost' => 40, 'price' => 60, 'stock' => 0, 'min' => 5],
            ['name' => 'Graph Paper Pad', 'sku' => 'SHR-007', 'category' => 'Stationery', 'brand' => 'Matador', 'barcode' => '8801002001007', 'cost' => 50, 'price' => 75, 'stock' => 0, 'min' => 7],
            ['name' => 'Correction Pen', 'sku' => 'SHR-008', 'category' => 'Stationery', 'brand' => 'Tipp-Ex', 'barcode' => '8801002001008', 'cost' => 55, 'price' => 85, 'stock' => 0, 'min' => 4],
            ['name' => 'Sticky Notes 3x3', 'sku' => 'SHR-009', 'category' => 'Stationery', 'brand' => 'Post-it', 'barcode' => '8801002001009', 'cost' => 65, 'price' => 95, 'stock' => 0, 'min' => 9],
            ['name' => 'Ruler 30cm', 'sku' => 'SHR-010', 'category' => 'Stationery', 'brand' => 'Deli', 'barcode' => '8801002001010', 'cost' => 15, 'price' => 25, 'stock' => 0, 'min' => 15],
        ];

        $created = 0;
        $updated = 0;

        foreach ($catalog as $row) {
            $exists = Product::where('user_id', $user->id)->where('sku', $row['sku'])->first();
            if ($exists) {
                $exists->update(['stock' => $row['stock'], 'min_stock' => $row['min']]);
                $updated++;

                continue;
            }

            Product::create([
                'user_id' => $user->id,
                'name' => $row['name'],
                'sku' => $row['sku'],
                'category' => $row['category'],
                'brand' => $row['brand'],
                'unit' => 'Pcs',
                'barcode' => $row['barcode'],
                'cost_price' => $row['cost'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'min_stock' => $row['min'],
            ]);
            $created++;
        }

        // Mark a few in-stock products as shortage for variety
        $inStock = Product::where('user_id', $user->id)
            ->whereColumn('stock', '>', 'min_stock')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($inStock as $product) {
            $product->update(['stock' => max(0, (int) floor($product->min_stock / 2))]);
            $updated++;
        }

        $totalShort = Product::where('user_id', $user->id)->whereColumn('stock', '<=', 'min_stock')->count();

        return compact('created', 'updated', 'totalShort');
    }

    private function clearUserData(User $user): void
    {
        DB::table('sale_return_items')->whereIn('sale_return_id', function ($q) use ($user) {
            $q->select('id')->from('sale_returns')->where('user_id', $user->id);
        })->delete();
        DB::table('sale_returns')->where('user_id', $user->id)->delete();
        DB::table('sale_items')->whereIn('sale_id', function ($q) use ($user) {
            $q->select('id')->from('sales')->where('user_id', $user->id);
        })->delete();
        DB::table('sales')->where('user_id', $user->id)->delete();

        DB::table('purchase_return_items')->whereIn('purchase_return_id', function ($q) use ($user) {
            $q->select('id')->from('purchase_returns')->where('user_id', $user->id);
        })->delete();
        DB::table('purchase_returns')->where('user_id', $user->id)->delete();
        DB::table('purchase_order_items')->whereIn('purchase_order_id', function ($q) use ($user) {
            $q->select('id')->from('purchase_orders')->where('user_id', $user->id);
        })->delete();
        DB::table('purchase_orders')->where('user_id', $user->id)->delete();
        DB::table('purchase_items')->whereIn('purchase_id', function ($q) use ($user) {
            $q->select('id')->from('purchases')->where('user_id', $user->id);
        })->delete();
        DB::table('purchases')->where('user_id', $user->id)->delete();

        Transaction::where('user_id', $user->id)->delete();
        Product::where('user_id', $user->id)->delete();
        Customer::where('user_id', $user->id)->delete();
        Supplier::where('user_id', $user->id)->delete();

        Account::where('user_id', $user->id)->update([
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);
    }

    private function seedShopSettings(User $user): void
    {
        ShopSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'Alphainno Demo Store',
                'warehouse_name' => 'Main Warehouse',
                'currency' => '৳',
                'default_tax_rate' => 5,
                'address' => '12 Gulshan Avenue, Dhaka 1212',
                'phone' => '+880 1712-345678',
                'email' => $user->email,
            ]
        );
    }

    /** @return list<Supplier> */
    private function seedSuppliers(User $user): array
    {
        $rows = [
            ['name' => 'TechSource BD', 'email' => 'sales@techsource.bd', 'phone' => '01711111111', 'address' => 'Motijheel, Dhaka'],
            ['name' => 'Fresh Foods Ltd', 'email' => 'order@freshfoods.bd', 'phone' => '01722222222', 'address' => 'Chittagong Port Area'],
            ['name' => 'StyleWear Trading', 'email' => 'info@stylewear.bd', 'phone' => '01733333333', 'address' => 'Uttara, Dhaka'],
        ];

        return array_map(fn ($row) => Supplier::create([...$row, 'user_id' => $user->id]), $rows);
    }

    /** @return list<Customer> */
    private function seedCustomers(User $user): array
    {
        $rows = [
            ['name' => 'Walk-in Retail', 'mobile' => '01800000001', 'email' => 'retail@example.com', 'address' => 'Dhaka', 'billing_city' => 'Dhaka', 'billing_country' => 'Bangladesh'],
            ['name' => 'Rahim Enterprise', 'mobile' => '01800000002', 'email' => 'rahim@enterprise.bd', 'address' => 'Mirpur-10', 'billing_city' => 'Dhaka', 'billing_country' => 'Bangladesh'],
            ['name' => 'Sadia Mart', 'mobile' => '01800000003', 'email' => 'sadia@mart.bd', 'address' => 'Banani', 'billing_city' => 'Dhaka', 'billing_country' => 'Bangladesh'],
            ['name' => 'Karim & Sons', 'mobile' => '01800000004', 'email' => 'karim@sons.bd', 'address' => 'Sylhet City', 'billing_city' => 'Sylhet', 'billing_country' => 'Bangladesh'],
            ['name' => 'Green Valley Shop', 'mobile' => '01800000005', 'email' => 'green@valley.bd', 'address' => 'Rajshahi', 'billing_city' => 'Rajshahi', 'billing_country' => 'Bangladesh'],
        ];

        return array_map(fn ($row) => Customer::create([...$row, 'user_id' => $user->id, 'phone' => $row['mobile']]), $rows);
    }

    /** @return list<Product> */
    private function seedProducts(User $user): array
    {
        $catalog = [
            ['name' => 'Samsung Galaxy Buds FE', 'sku' => 'ELC-001', 'category' => 'Electronics', 'brand' => 'Samsung', 'barcode' => '8801001001001', 'cost' => 4500, 'price' => 5990, 'stock' => 0, 'min' => 3],
            ['name' => 'Xiaomi Power Bank 20000mAh', 'sku' => 'ELC-002', 'category' => 'Electronics', 'brand' => 'Xiaomi', 'barcode' => '8801001001002', 'cost' => 1200, 'price' => 1690, 'stock' => 0, 'min' => 5],
            ['name' => 'HP USB Keyboard K500', 'sku' => 'ELC-003', 'category' => 'Electronics', 'brand' => 'HP', 'barcode' => '8801001001003', 'cost' => 850, 'price' => 1190, 'stock' => 0, 'min' => 4],
            ['name' => 'Basmati Rice Premium 5kg', 'sku' => 'GRO-001', 'category' => 'Grocery', 'brand' => 'ACI', 'barcode' => '8801001002001', 'cost' => 520, 'price' => 650, 'stock' => 0, 'min' => 10],
            ['name' => 'Soybean Oil 5 Liter', 'sku' => 'GRO-002', 'category' => 'Grocery', 'brand' => 'Fresh', 'barcode' => '8801001002002', 'cost' => 680, 'price' => 780, 'stock' => 0, 'min' => 8],
            ['name' => 'Fresh Milk 1 Liter', 'sku' => 'GRO-003', 'category' => 'Grocery', 'brand' => 'Milk Vita', 'barcode' => '8801001002003', 'cost' => 75, 'price' => 95, 'stock' => 0, 'min' => 20],
            ['name' => 'Pran Cream Biscuit 150g', 'sku' => 'GRO-004', 'category' => 'Grocery', 'brand' => 'Pran', 'barcode' => '8801001002004', 'cost' => 35, 'price' => 45, 'stock' => 0, 'min' => 30],
            ['name' => 'Cotton T-Shirt (M)', 'sku' => 'CLT-001', 'category' => 'Clothing', 'brand' => 'Yellow', 'barcode' => '8801001003001', 'cost' => 280, 'price' => 450, 'stock' => 0, 'min' => 6],
            ['name' => 'Denim Jeans (32)', 'sku' => 'CLT-002', 'category' => 'Clothing', 'brand' => 'Levis', 'barcode' => '8801001003002', 'cost' => 1100, 'price' => 1890, 'stock' => 0, 'min' => 4],
            ['name' => 'Sports Cap', 'sku' => 'CLT-003', 'category' => 'Clothing', 'brand' => 'Adidas', 'barcode' => '8801001003003', 'cost' => 180, 'price' => 350, 'stock' => 0, 'min' => 5],
            ['name' => 'A4 Notebook 200 Pages', 'sku' => 'STN-001', 'category' => 'Stationery', 'brand' => 'Matador', 'barcode' => '8801001004001', 'cost' => 55, 'price' => 80, 'stock' => 0, 'min' => 15],
            ['name' => 'Ball Pen Pack (10 pcs)', 'sku' => 'STN-002', 'category' => 'Stationery', 'brand' => 'Matador', 'barcode' => '8801001004002', 'cost' => 90, 'price' => 130, 'stock' => 0, 'min' => 10],
            ['name' => 'Desktop Stapler', 'sku' => 'STN-003', 'category' => 'Stationery', 'brand' => 'Deli', 'barcode' => '8801001004003', 'cost' => 120, 'price' => 180, 'stock' => 0, 'min' => 5],
            ['name' => 'Wireless Mouse', 'sku' => 'ELC-004', 'category' => 'Electronics', 'brand' => 'Logitech', 'barcode' => '8801001001004', 'cost' => 650, 'price' => 890, 'stock' => 0, 'min' => 5],
            ['name' => 'Instant Noodles (5 pack)', 'sku' => 'GRO-005', 'category' => 'Grocery', 'brand' => 'Maggi', 'barcode' => '8801001002005', 'cost' => 110, 'price' => 140, 'stock' => 0, 'min' => 25],
            ['name' => 'Mineral Water 1.5L', 'sku' => 'GRO-006', 'category' => 'Grocery', 'brand' => 'MUM', 'barcode' => '8801001002006', 'cost' => 22, 'price' => 30, 'stock' => 0, 'min' => 40],
        ];

        return array_map(function ($row) use ($user) {
            return Product::create([
                'user_id' => $user->id,
                'name' => $row['name'],
                'sku' => $row['sku'],
                'category' => $row['category'],
                'brand' => $row['brand'],
                'unit' => 'Pcs',
                'barcode' => $row['barcode'],
                'cost_price' => $row['cost'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'min_stock' => $row['min'],
            ]);
        }, $catalog);
    }

    /**
     * @param  list<Supplier>  $suppliers
     * @param  list<Product>  $products
     * @return list<Purchase>
     */
    private function seedPurchases(User $user, array $suppliers, array $products): array
    {
        $plans = [
            ['days_ago' => 75, 'supplier' => 0, 'paid_ratio' => 1, 'items' => [[0, 15], [1, 20], [2, 12]]],
            ['days_ago' => 52, 'supplier' => 1, 'paid_ratio' => 0.6, 'items' => [[3, 50], [4, 40], [5, 100], [6, 80]]],
            ['days_ago' => 35, 'supplier' => 2, 'paid_ratio' => 1, 'items' => [[7, 25], [8, 15], [9, 20]]],
            ['days_ago' => 18, 'supplier' => 0, 'paid_ratio' => 0.5, 'items' => [[10, 40], [11, 30], [12, 20], [13, 18]]],
            ['days_ago' => 5, 'supplier' => 1, 'paid_ratio' => 1, 'items' => [[14, 60], [15, 120], [0, 8]]],
        ];

        $created = [];
        $num = Purchase::where('user_id', $user->id)->count();

        foreach ($plans as $plan) {
            $date = now()->subDays($plan['days_ago'])->toDateString();
            $total = 0;
            $lines = [];

            foreach ($plan['items'] as [$idx, $qty]) {
                $product = $products[$idx];
                $sub = round($product->cost_price * $qty, 2);
                $total += $sub;
                $lines[] = compact('product', 'qty', 'sub');
            }

            $paid = round($total * $plan['paid_ratio'], 2);
            $due = max(round($total - $paid, 2), 0);
            $num++;

            $purchase = Purchase::create([
                'user_id' => $user->id,
                'supplier_id' => $suppliers[$plan['supplier']]->id,
                'reference' => 'PO#'.str_pad((string) $num, 8, '0', STR_PAD_LEFT),
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
                'purchase_date' => $date,
                'notes' => 'Demo purchase invoice',
            ]);

            foreach ($lines as $line) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'unit_cost' => $line['product']->cost_price,
                    'subtotal' => $line['sub'],
                ]);
                $line['product']->increment('stock', $line['qty']);
            }

            if ($paid > 0) {
                $this->recordTransaction($user, '1000', 'debit', $paid, $purchase->reference, 'Purchase payment', $date, Purchase::class, $purchase->id);
            }

            $created[] = $purchase;
        }

        return $created;
    }

    /**
     * @param  list<Customer>  $customers
     * @param  list<Product>  $products
     * @return list<Sale>
     */
    private function seedSales(User $user, array $customers, array $products): array
    {
        $taxRate = 5.0;
        $warehouse = 'Main Warehouse';
        $plans = [
            ['days_ago' => 60, 'customer' => 1, 'paid_ratio' => 1, 'items' => [[0, 2], [13, 3]]],
            ['days_ago' => 48, 'customer' => 2, 'paid_ratio' => 1, 'items' => [[3, 5], [4, 3], [15, 10]]],
            ['days_ago' => 40, 'customer' => 0, 'paid_ratio' => 1, 'items' => [[5, 6], [6, 8]]],
            ['days_ago' => 32, 'customer' => 3, 'paid_ratio' => 0.7, 'items' => [[7, 2], [8, 1], [9, 3]]],
            ['days_ago' => 28, 'customer' => 2, 'paid_ratio' => 1, 'items' => [[1, 4], [2, 2]]],
            ['days_ago' => 21, 'customer' => 4, 'paid_ratio' => 0.5, 'items' => [[10, 6], [11, 4], [12, 2]]],
            ['days_ago' => 14, 'customer' => 1, 'paid_ratio' => 1, 'items' => [[14, 10], [15, 24]]],
            ['days_ago' => 10, 'customer' => 0, 'paid_ratio' => 1, 'items' => [[0, 1], [13, 1], [1, 2]]],
            ['days_ago' => 7, 'customer' => 3, 'paid_ratio' => 1, 'items' => [[3, 3], [4, 2]]],
            ['days_ago' => 4, 'customer' => 2, 'paid_ratio' => 0.8, 'items' => [[7, 1], [8, 1], [9, 2]]],
            ['days_ago' => 2, 'customer' => 0, 'paid_ratio' => 1, 'items' => [[5, 4], [6, 6], [15, 8]]],
            ['days_ago' => 1, 'customer' => 4, 'paid_ratio' => 1, 'items' => [[10, 3], [11, 2], [12, 1]]],
        ];

        $created = [];
        $num = Sale::where('user_id', $user->id)->count();

        foreach ($plans as $plan) {
            $date = now()->subDays($plan['days_ago'])->toDateString();
            $subtotal = 0;
            $totalTax = 0;
            $lines = [];

            foreach ($plan['items'] as [$idx, $qty]) {
                $product = $products[$idx];
                if ($product->stock < $qty) {
                    continue;
                }
                $lineSub = round($product->price * $qty, 2);
                $lineTax = round($lineSub * ($taxRate / 100), 2);
                $subtotal += $lineSub;
                $totalTax += $lineTax;
                $lines[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'lineSub' => $lineSub,
                    'lineTax' => $lineTax,
                    'lineTotal' => $lineSub + $lineTax,
                ];
            }

            if ($lines === []) {
                continue;
            }

            $grandTotal = round($subtotal + $totalTax, 2);
            $paid = round($grandTotal * $plan['paid_ratio'], 2);
            $due = max(round($grandTotal - $paid, 2), 0);
            $num++;

            $sale = Sale::create([
                'user_id' => $user->id,
                'customer_id' => $customers[$plan['customer']]->id,
                'reference' => 'SO#'.str_pad((string) $num, 8, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => 0,
                'total' => $grandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'due'),
                'payment_method' => ['cash', 'card', 'mobile'][$num % 3],
                'warehouse' => $warehouse,
                'delivery_status' => 'delivered',
                'sale_date' => $date,
            ]);

            foreach ($lines as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit' => 'Pcs',
                    'quantity' => $line['qty'],
                    'unit_price' => $line['product']->price,
                    'discount' => 0,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $line['lineTax'],
                    'subtotal' => $line['lineTotal'],
                ]);
                $line['product']->decrement('stock', $line['qty']);
            }

            if ($paid > 0) {
                $this->recordTransaction($user, '1000', 'credit', $paid, $sale->reference, 'POS sale (demo)', $date, Sale::class, $sale->id);
                $this->recordTransaction($user, '4000', 'credit', $paid, $sale->reference, 'Sales revenue', $date, Sale::class, $sale->id);
            }

            $created[] = $sale;
        }

        return $created;
    }

    /** @param  list<Supplier>  $suppliers  @param  list<Product>  $products */
    private function seedPurchaseOrders(User $user, array $suppliers, array $products): void
    {
        $order = PurchaseOrder::create([
            'user_id' => $user->id,
            'supplier_id' => $suppliers[0]->id,
            'reference' => 'PORD-'.now()->format('Ymd').'-001',
            'total' => 12500,
            'status' => 'pending',
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Demo purchase order — awaiting delivery',
        ]);

        foreach ([[0, 10], [13, 15]] as [$idx, $qty]) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $products[$idx]->id,
                'product_name' => $products[$idx]->name,
                'quantity' => $qty,
                'unit_cost' => $products[$idx]->cost_price,
                'subtotal' => round($products[$idx]->cost_price * $qty, 2),
            ]);
        }

        PurchaseOrder::create([
            'user_id' => $user->id,
            'supplier_id' => $suppliers[1]->id,
            'reference' => 'PORD-'.now()->format('Ymd').'-002',
            'total' => 8900,
            'status' => 'approved',
            'order_date' => now()->subDays(3)->toDateString(),
            'expected_date' => now()->addDays(4)->toDateString(),
            'notes' => 'Demo grocery restock order',
        ]);
    }

    private function recordTransaction(
        User $user,
        string $accountCode,
        string $type,
        float $amount,
        string $reference,
        string $description,
        string $date,
        string $relatedType,
        int $relatedId
    ): void {
        $account = Account::where('user_id', $user->id)->where('code', $accountCode)->first();
        if (! $account) {
            return;
        }

        Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'type' => $type,
            'amount' => $amount,
            'reference' => $reference,
            'description' => $description,
            'transaction_date' => $date,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);

        if ($type === 'credit') {
            $account->increment('current_balance', $amount);
        } else {
            $account->decrement('current_balance', $amount);
        }
    }
}
