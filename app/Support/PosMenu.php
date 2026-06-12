<?php

namespace App\Support;

class PosMenu
{
    /** @return array<int, array<string, mixed>> */
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard.index',
            ],
            [
                'label' => 'POS',
                'icon' => 'cart',
                'route' => 'pos.index',
            ],
            [
                'label' => 'Inventory',
                'icon' => 'box',
                'children' => [
                    ['label' => 'Product', 'route' => 'products.index'],
                    ['label' => 'Shortage Products', 'route' => 'inventory.shortage'],
                ],
            ],
            [
                'label' => 'Purchase',
                'icon' => 'purchase',
                'children' => [
                    ['label' => 'Purchase Invoice', 'route' => 'purchases.index'],
                    ['label' => 'Suppliers', 'route' => 'suppliers.index'],
                    ['label' => 'Purchase Return', 'route' => 'purchase-returns.index'],
                    ['label' => 'Purchase Order', 'route' => 'purchase-orders.index'],
                ],
            ],
            [
                'label' => 'Sale',
                'icon' => 'sale',
                'children' => [
                    ['label' => 'Sale Invoice', 'route' => 'sales.index'],
                    ['label' => 'Customers', 'route' => 'customers.index'],
                    ['label' => 'Sale Return', 'route' => 'sale-returns.index'],
                ],
            ],
            [
                'label' => 'Accounts',
                'icon' => 'wallet',
                'children' => [
                    ['label' => 'Account', 'route' => 'accounts.index'],
                    ['label' => 'Transaction', 'route' => 'transactions.index'],
                    ['label' => 'Trial Balance', 'route' => 'accounts.trial-balance'],
                    ['label' => 'Balance Sheet', 'route' => 'accounts.balance-sheet'],
                    ['label' => 'Income Statement', 'route' => 'accounts.income-statement'],
                ],
            ],
            [
                'label' => 'Report',
                'icon' => 'report',
                'children' => [
                    ['label' => 'Inventory Report', 'route' => 'reports.inventory'],
                    ['label' => 'Purchase Report', 'route' => 'reports.purchase'],
                    ['label' => 'Sale Report', 'route' => 'reports.sale'],
                    ['label' => 'Supplier Report', 'route' => 'reports.supplier'],
                    ['label' => 'Customer Report', 'route' => 'reports.customer'],
                    ['label' => 'Payment Report', 'route' => 'reports.payment'],
                ],
            ],
            [
                'label' => 'Settings',
                'icon' => 'settings',
                'route' => 'settings.index',
            ],
        ];
    }
}
