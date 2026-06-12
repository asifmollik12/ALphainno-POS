<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ShopSetting;
use App\Models\User;

class ShopBootstrapService
{
    public function ensureDefaults(User $user): void
    {
        ShopSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $user->name,
                'currency' => '৳',
            ]
        );

        if ($user->accounts()->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Cash', 'code' => '1000', 'type' => 'asset'],
            ['name' => 'Bank', 'code' => '1010', 'type' => 'asset'],
            ['name' => 'Accounts Receivable', 'code' => '1100', 'type' => 'asset'],
            ['name' => 'Inventory', 'code' => '1200', 'type' => 'asset'],
            ['name' => 'Accounts Payable', 'code' => '2000', 'type' => 'liability'],
            ['name' => 'Sales Revenue', 'code' => '4000', 'type' => 'income'],
            ['name' => 'Cost of Goods Sold', 'code' => '5000', 'type' => 'expense'],
            ['name' => 'Operating Expense', 'code' => '5100', 'type' => 'expense'],
        ];

        foreach ($defaults as $row) {
            Account::create([
                'user_id' => $user->id,
                'name' => $row['name'],
                'code' => $row['code'],
                'type' => $row['type'],
                'opening_balance' => 0,
                'current_balance' => 0,
            ]);
        }
    }
}
