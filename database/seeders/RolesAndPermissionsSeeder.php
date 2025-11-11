<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\PermissionGroup;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $records = [
            [
                'groupName' => 'Customers',
                'permissionName' => [
                    [
                        'name' => 'customer.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'customer.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'customer.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'customer.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Tax',
                'permissionName' => [
                    [
                        'name' => 'tax.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'tax.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'tax.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'tax.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Users',
                'permissionName' => [
                    [
                        'name' => 'user.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'user.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'user.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'user.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Roles',
                'permissionName' => [
                    [
                        'name' => 'role.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'role.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'role.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'role.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Profile',
                'permissionName' => [
                    [
                        'name' => 'profile.edit',
                        'displayName' => 'Edit',
                    ],
                ],
            ],
            [
                'groupName' => 'App Settings',
                'permissionName' => [
                    [
                        'name' => 'app.settings.edit',
                        'displayName' => 'Edit',
                    ],
                ],
            ],
            [
                'groupName' => 'Bank Account',
                'permissionName' => [
                    [
                        'name' => 'payment.type.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'payment.type.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'payment.type.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'payment.type.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Company Details',
                'permissionName' => [
                    [
                        'name' => 'company.edit',
                        'displayName' => 'Edit',
                    ],
                ],
            ],
            [
                'groupName' => 'Create & Send Manual SMS',
                'permissionName' => [
                    [
                        'name' => 'sms.create',
                        'displayName' => 'Create',
                    ],
                ],
            ],
            [
                'groupName' => 'SMS Template',
                'permissionName' => [
                    [
                        'name' => 'sms.template.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'sms.template.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'sms.template.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'sms.template.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Create & Send Manual Email',
                'permissionName' => [
                    [
                        'name' => 'email.create',
                        'displayName' => 'Create',
                    ],
                ],
            ],
            [
                'groupName' => 'Email Template',
                'permissionName' => [
                    [
                        'name' => 'email.template.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'email.template.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'email.template.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'email.template.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Languages',
                'permissionName' => [
                    [
                        'name' => 'language.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'language.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'language.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'language.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Reports',
                'permissionName' => [
                    [
                        'name' => 'report.profit_and_loss',
                        'displayName' => 'Profit and Loss',
                    ],
                    [
                        'name' => 'report.item.transaction.batch',
                        'displayName' => 'Batch Wise Item Transaction Report',
                    ],
                    [
                        'name' => 'report.item.transaction.serial',
                        'displayName' => 'Serial/IMEI Item Transaction Report',
                    ],
                    [
                        'name' => 'report.item.transaction.general',
                        'displayName' => 'General Item Transaction Report',
                    ],
                    [
                        'name' => 'report.purchase',
                        'displayName' => 'Purchase Report',
                    ],
                    [
                        'name' => 'report.purchase.item',
                        'displayName' => 'Item Purchase Report',
                    ],
                    [
                        'name' => 'report.purchase.payment',
                        'displayName' => 'Purchase Payment Report',
                    ],
                    [
                        'name' => 'report.sale',
                        'displayName' => 'Sale Report',
                    ],
                    [
                        'name' => 'report.sale.item',
                        'displayName' => 'Item Sale Report',
                    ],
                    [
                        'name' => 'report.sale.payment',
                        'displayName' => 'Sale Payment Report',
                    ],
                    [
                        'name' => 'report.expired.item',
                        'displayName' => 'Expired Item Report',
                    ],
                    [
                        'name' => 'report.reorder.item',
                        'displayName' => 'Reorder Item Report',
                    ],
                    [
                        'name' => 'report.expense',
                        'displayName' => 'Expense Report',
                    ],
                    [
                        'name' => 'report.expense.item',
                        'displayName' => 'Item Expense Report',
                    ],
                    [
                        'name' => 'report.expense.payment',
                        'displayName' => 'Expense Payment Report',
                    ],
                    [
                        'name' => 'report.gstr-1',
                        'displayName' => 'GSTR-1',
                    ],
                    [
                        'name' => 'report.gstr-2',
                        'displayName' => 'GSTR-2',
                    ],
                    [
                        'name' => 'report.stock_transfer',
                        'displayName' => 'Stock Transfer Report',
                    ],
                    [
                        'name' => 'report.stock_transfer.item',
                        'displayName' => 'Item Stock Transfer Report',
                    ],
                    [
                        'name' => 'report.customer.due.payment',
                        'displayName' => 'Customer Payments Due Report',
                    ],
                    [
                        'name' => 'report.supplier.due.payment',
                        'displayName' => 'Supplier Payments Due Report',
                    ],
                    [
                        'name' => 'report.stock_report.item.batch',
                        'displayName' => 'Batch Wise Item Stock Report',
                    ],
                    [
                        'name' => 'report.stock_report.item.serial',
                        'displayName' => 'Serial Wise Item Stock Report',
                    ],
                    [
                        'name' => 'report.stock_report.item.general',
                        'displayName' => 'General Item Stock Report',
                    ],
                    [
                        'name' => 'report.stock_adjustment',
                        'displayName' => 'Stock Adjustment Report',
                    ],
                    [
                        'name' => 'report.stock_adjustment.item',
                        'displayName' => 'Item Wise Stock Adjustment Report',
                    ],
                ],
            ],
            [
                'groupName' => 'Expense',
                'permissionName' => [
                    [
                        'name' => 'expense.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'expense.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'expense.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'expense.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'expense.category.create',
                        'displayName' => 'Category Create',
                    ],
                    [
                        'name' => 'expense.category.edit',
                        'displayName' => 'Category Edit',
                    ],
                    [
                        'name' => 'expense.category.view',
                        'displayName' => 'Category View',
                    ],
                    [
                        'name' => 'expense.category.delete',
                        'displayName' => 'Category Delete',
                    ],
                    [
                        'name' => 'expense.subcategory.create',
                        'displayName' => 'Expense Subcategory Create',
                    ],
                    [
                        'name' => 'expense.subcategory.edit',
                        'displayName' => 'Expense Subcategory Edit',
                    ],
                    [
                        'name' => 'expense.subcategory.view',
                        'displayName' => 'Expense Subcategory View',
                    ],
                    [
                        'name' => 'expense.subcategory.delete',
                        'displayName' => 'Expense Subcategory Delete',
                    ],
                    [
                        'name' => 'expense.can.view.other.users.expenses',
                        'displayName' => 'Allow User to View All Expenses Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Warehouses',
                'permissionName' => [
                    [
                        'name' => 'warehouse.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'warehouse.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'warehouse.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'warehouse.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Stock Transfer',
                'permissionName' => [
                    [
                        'name' => 'stock_transfer.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'stock_transfer.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'stock_transfer.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'stock_transfer.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'stock_transfer.can.view.other.users.stock.transfers',
                        'displayName' => 'Allow User to View All Stock Transfer Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Items',
                'permissionName' => [
                    [
                        'name' => 'item.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'item.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'item.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'item.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'item.category.create',
                        'displayName' => 'Category Create',
                    ],
                    [
                        'name' => 'item.category.edit',
                        'displayName' => 'Category Edit',
                    ],
                    [
                        'name' => 'item.category.view',
                        'displayName' => 'Category View',
                    ],
                    [
                        'name' => 'item.category.delete',
                        'displayName' => 'Category Delete',
                    ],
                    [
                        'name' => 'item.brand.create',
                        'displayName' => 'Brand Create',
                    ],
                    [
                        'name' => 'item.brand.edit',
                        'displayName' => 'Brand Edit',
                    ],
                    [
                        'name' => 'item.brand.view',
                        'displayName' => 'Brand View',
                    ],
                    [
                        'name' => 'item.brand.delete',
                        'displayName' => 'Brand Delete',
                    ],
                ],
            ],
            [
                'groupName' => 'Units',
                'permissionName' => [
                    [
                        'name' => 'unit.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'unit.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'unit.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'unit.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Suppliers',
                'permissionName' => [
                    [
                        'name' => 'supplier.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'supplier.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'supplier.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'supplier.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Utilities',
                'permissionName' => [
                    [
                        'name' => 'import.item',
                        'displayName' => 'Import Items & Services',
                    ],
                    [
                        'name' => 'import.party',
                        'displayName' => 'Import Customers & Suppliers',
                    ],
                    [
                        'name' => 'generate.barcode',
                        'displayName' => 'Generate Barcode',
                    ],
                ],
            ],
            [
                'groupName' => 'Purchase Order',
                'permissionName' => [
                    [
                        'name' => 'purchase.order.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'purchase.order.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'purchase.order.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'purchase.order.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'purchase.order.can.view.other.users.purchase.orders',
                        'displayName' => 'Allow User to View All Purchase Orders Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Purchase Bill',
                'permissionName' => [
                    [
                        'name' => 'purchase.bill.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'purchase.bill.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'purchase.bill.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'purchase.bill.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'purchase.bill.can.view.other.users.purchase.bills',
                        'displayName' => 'Allow User to View All Purchase Bills Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Purchase Return',
                'permissionName' => [
                    [
                        'name' => 'purchase.return.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'purchase.return.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'purchase.return.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'purchase.return.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'purchase.return.can.view.other.users.purchase.returns',
                        'displayName' => 'Allow User to View All Purchase Returns Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Sale Order',
                'permissionName' => [
                    [
                        'name' => 'sale.order.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'sale.order.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'sale.order.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'sale.order.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'sale.order.can.view.other.users.sale.orders',
                        'displayName' => 'Allow User to View All Sale Orders Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Sale Bill',
                'permissionName' => [
                    [
                        'name' => 'sale.bill.view',
                        'displayName' => 'View Sale Invoices Menu',
                    ],
                    [
                        'name' => 'sale.bill.create',
                        'displayName' => 'Create Sale Invoice Button',
                    ],
                    [
                        'name' => 'sale.invoice.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'sale.invoice.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'sale.invoice.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'sale.invoice.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'sale.invoice.can.view.other.users.sale.invoices',
                        'displayName' => 'Allow User to View All Sale Invoices Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Sale Return',
                'permissionName' => [
                    [
                        'name' => 'sale.return.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'sale.return.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'sale.return.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'sale.return.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'sale.return.can.view.other.users.sale.returns',
                        'displayName' => 'Allow User to View All Sale Returns Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Cash & Bank Transaction',
                'permissionName' => [
                    [
                        'name' => 'transaction.cash.add',
                        'displayName' => 'Cash Transaction Create',
                    ],
                    [
                        'name' => 'transaction.cash.edit',
                        'displayName' => 'Cash Transaction Edit',
                    ],
                    [
                        'name' => 'transaction.cash.view',
                        'displayName' => 'Cash Transaction View',
                    ],
                    [
                        'name' => 'transaction.cash.delete',
                        'displayName' => 'Cash Transaction Delete',
                    ],
                    [
                        'name' => 'transaction.bank.view',
                        'displayName' => 'Bank Transaction View',
                    ],
                    [
                        'name' => 'transaction.cheque.view',
                        'displayName' => 'Cheque Transaction View',
                    ],
                    // New permissions for Close Cash functionality
                    [
                        'name' => 'transaction.cash.close',
                        'displayName' => 'Close Cash',
                    ],
                    [
                        'name' => 'transaction.cash.close.list',
                        'displayName' => 'Close Cash List',
                    ],
                    [
                        'name' => 'transaction.cash.close.list.can.view.other.users.close.cash.list',
                        'displayName' => 'Allow User to View Close Cash List Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'General',
                'permissionName' => [
                    [
                        'name' => 'general.allow.to.view.item.purchase.price',
                        'displayName' => 'Allow User to View Item Purchase Price in Item Search(Invoice/Bill)',
                    ],
                    [
                        'name' => 'general.permission.to.apply.discount.to.sale',
                        'displayName' => 'Permission to Apply Discounts on Invoices',
                    ],
                    [
                        'name' => 'general.permission.to.apply.discount.to.purchase',
                        'displayName' => 'Permission to Apply Discounts on Purchases',
                    ],
                ],
            ],
            [
                'groupName' => 'Dashboard',
                'permissionName' => [
                    [
                        'name' => 'dashboard.can.view.widget.cards',
                        'displayName' => 'Allow User to View Dashboard Widget Cards',
                    ],
                    [
                        'name' => 'dashboard.can.view.sale.vs.purchase.bar.chart',
                        'displayName' => 'Allow User to View Sale Vs. Purchase Bar Chart on Dashboard',
                    ],
                    [
                        'name' => 'dashboard.can.view.trending.items.pie.chart',
                        'displayName' => 'Allow User to View Trending Items Pie Chart on Dashboard',
                    ],
                    [
                        'name' => 'dashboard.can.view.recent.invoices.table',
                        'displayName' => 'Allow User to View Recent Invoices Table on Dashboard',
                    ],
                    [
                        'name' => 'dashboard.can.view.self.dashboard.details.only',
                        'displayName' => 'Allow User to View Only Their Own Dashboard Details',
                    ],
                ],
            ],
            [
                'groupName' => 'Quotation',
                'permissionName' => [
                    [
                        'name' => 'sale.quotation.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'sale.quotation.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'sale.quotation.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'sale.quotation.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'sale.quotation.can.view.other.users.sale.quotations',
                        'displayName' => 'Allow User to View All Quotations Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Currency',
                'permissionName' => [
                    [
                        'name' => 'currency.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'currency.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'currency.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'currency.delete',
                        'displayName' => 'Delete',
                    ],
                ],
            ],
            [
                'groupName' => 'Carrier',
                'permissionName' => [
                    [
                        'name' => 'carrier.create',
                        'displayName' => 'Carrier Create',
                    ],
                    [
                        'name' => 'carrier.edit',
                        'displayName' => 'Carrier Edit',
                    ],
                    [
                        'name' => 'carrier.view',
                        'displayName' => 'Carrier View',
                    ],
                    [
                        'name' => 'carrier.delete',
                        'displayName' => 'Carrier Delete',
                    ],
                ],
            ],
            [
                'groupName' => 'Stock Adjustment',
                'permissionName' => [
                    [
                        'name' => 'stock_adjustment.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'stock_adjustment.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'stock_adjustment.delete',
                        'displayName' => 'Delete',
                    ],
                    [
                        'name' => 'stock_adjustment.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'stock_adjustment.can.view.other.users.stock.adjustments',
                        'displayName' => 'Allow User to View All Stock Adjustment Created By Other Users',
                    ],
                ],
            ],
            [
                'groupName' => 'Registers',
                'permissionName' => [
                    [
                        'name' => 'register.create',
                        'displayName' => 'Create',
                    ],
                    [
                        'name' => 'register.edit',
                        'displayName' => 'Edit',
                    ],
                    [
                        'name' => 'register.view',
                        'displayName' => 'View',
                    ],
                    [
                        'name' => 'register.delete',
                        'displayName' => 'Delete',
                    ]
                ],
            ],
            [
                'groupName' => 'Session',
                'permissionName' => [
                    [
                        'name' => 'session.view',
                        'displayName' => 'View Sessions',
                    ],
                    [
                        'name' => 'session.create',
                        'displayName' => 'Create Session',
                    ],
                    [
                        'name' => 'session.edit',
                        'displayName' => 'Edit Session',
                    ],
                    [
                        'name' => 'session.delete',
                        'displayName' => 'Delete Session',
                    ],
                ],
            ],
            [
                'groupName' => 'System Maintenance',
                'permissionName' => [
                    [
                        'name' => 'system.clear_cache',
                        'displayName' => 'Clear Cache',
                    ],
                    [
                        'name' => 'system.database_backup',
                        'displayName' => 'Database Backup',
                    ],
                ],
            ],
            [
                'groupName' => 'Notifications',
                'permissionName' => [
                    [
                        'name' => 'notification.view',
                        'displayName' => 'View Notifications',
                    ],
                ],
            ],
            [
                'groupName' => 'App Log',
                'permissionName' => [
                    [
                        'name' => 'app.log.view',
                        'displayName' => 'View App Log',
                    ],
                    [
                        'name' => 'app.log.clear_all',
                        'displayName' => 'Clear All Logs',
                    ],
                ],
            ],
            [
                'groupName' => 'Database Backup',
                'permissionName' => [
                    [
                        'name' => 'database.backup.view',
                        'displayName' => 'View Database Backup',
                    ],
                ],
            ],
            [
                'groupName' => 'Customization',
                'permissionName' => [
                    [
                        'name' => 'customization.edit',
                        'displayName' => 'Edit Customization',
                    ],
                ],
            ],
        ];

        // Start database transaction for safety
        DB::beginTransaction();

        try {
            $totalGroups = 0;
            $totalPermissions = 0;

            foreach ($records as $record) {
                $group = \App\Models\PermissionGroup::firstOrCreate(['name' => $record['groupName']]);

                foreach ($record['permissionName'] as $permission) {
                    \Spatie\Permission\Models\Permission::firstOrCreate(
                        ['name' => $permission['name']],
                        [
                            'display_name' => $permission['displayName'],
                            'permission_group_id' => $group->id,
                            'status' => 1,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RolesAndPermissionsSeeder error: ' . $e->getMessage());
            throw $e;
        }
    }
}
