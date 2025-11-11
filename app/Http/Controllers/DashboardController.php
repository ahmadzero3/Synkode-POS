<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Sale\SaleOrder;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Customer;
use App\Models\Sale\Sale;
use App\Models\Sale\SaleReturn;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Party\Party;
use App\Models\Party\PartyTransaction;
use App\Models\Party\PartyPayment;
use App\Models\Expenses\Expense;
use App\Models\Items\ItemTransaction;
use App\Models\Items\Item;
use App\Models\PaymentTransaction;
use App\Traits\FormatNumber;

class DashboardController extends Controller
{
    use FormatNumber;

    public function index()
    {
        $pendingSaleOrders = SaleOrder::whereDoesntHave('sale')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $totalCompletedSaleOrders = Sale::where('invoice_status', 'finished')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $partyBalance = $this->paymentReceivables();
        $totalPaymentReceivables = $this->formatWithPrecision($partyBalance['receivable']);

        $totalSuppliers = Party::where('party_type', 'supplier')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $totalPaidAmountForFinishedInvoices = Sale::where('invoice_status', 'finished')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->sum('paid_amount');

        $totalPurchaseOrders = Purchase::when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
            return $query->where('created_by', auth()->user()->id);
        })
            ->count();

        $pendingPurchaseOrders = PurchaseOrder::whereDoesntHave('purchase')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $totalCompletedPurchaseOrders = PurchaseOrder::whereHas('purchase')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $pendingInvoices = Sale::where('invoice_status', 'pending')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $totalCustomers = Party::where('party_type', 'customer')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->count();

        $totalExpense = PaymentTransaction::where('transaction_type', 'Purchase')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($q) {
                return $q->where('created_by', auth()->user()->id);
            })
            ->sum('amount');

        $totalExpense = $this->formatWithPrecision($totalExpense);

        $recentInvoices = Sale::when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
            return $query->where('created_by', auth()->user()->id);
        })
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $saleVsPurchase = $this->saleVsPurchase();
        $trendingItems = $this->trendingItems();
        $lowStockItems = $this->getLowStockItemRecords();

        return view('dashboard', compact(
            'pendingSaleOrders',
            'pendingPurchaseOrders',
            'totalCompletedSaleOrders',
            'totalCompletedPurchaseOrders',
            'totalCustomers',
            'totalPaymentReceivables',
            'totalSuppliers',
            'totalExpense',
            'saleVsPurchase',
            'trendingItems',
            'lowStockItems',
            'recentInvoices',
            'pendingInvoices',
            'totalPaidAmountForFinishedInvoices',
            'totalPurchaseOrders',
            'totalPaidAmountForFinishedInvoices'
        ));
    }

    public function saleVsPurchase()
    {
        $labels = [];
        $sales = [];
        $purchases = [];

        $now = now();
        for ($i = 0; $i < 6; $i++) {
            $month = $now->copy()->subMonths($i)->format('M Y');
            $labels[] = $month;

            $sales[] = Sale::whereMonth('sale_date', $now->copy()->subMonths($i)->month)
                ->whereYear('sale_date', $now->copy()->subMonths($i)->year)
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $purchases[] = Purchase::whereMonth('purchase_date', $now->copy()->subMonths($i)->month)
                ->whereYear('purchase_date', $now->copy()->subMonths($i)->year)
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();
        }

        $labels = array_reverse($labels);
        $sales = array_reverse($sales);
        $purchases = array_reverse($purchases);

        $saleVsPurchase = [];

        for ($i = 0; $i < count($labels); $i++) {
            $saleVsPurchase[] = [
                'label' => $labels[$i],
                'sales' => $sales[$i],
                'purchases' => $purchases[$i],
            ];
        }

        return $saleVsPurchase;
    }

    public function trendingItems(): array
    {
        return ItemTransaction::query()
            ->select([
                'items.name',
                DB::raw('SUM(item_transactions.quantity) as total_quantity')
            ])
            ->join('items', 'items.id', '=', 'item_transactions.item_id')
            ->where('item_transactions.transaction_type', getMorphedModelName(Sale::class))
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('item_transactions.created_by', auth()->user()->id);
            })
            ->groupBy('item_transactions.item_id', 'items.name')
            ->orderByDesc('total_quantity')
            ->limit(4)
            ->get()
            ->toArray();
    }

    public function paymentReceivables()
    {
        $openingReceivable = PartyTransaction::whereIn('transaction_id', Party::select('id')->where('party_type', 'customer')->pluck('id'))
            ->where('transaction_type', 'Party Opening')
            ->selectRaw('COALESCE(SUM(to_receive) - SUM(to_pay), 0) as opening_payable')
            ->where('created_by', 3)
            ->first()
            ->opening_payable ?? 0;

        $openingPayable = PartyTransaction::whereIn('transaction_id', Party::select('id')->where('party_type', 'supplier')->pluck('id'))
            ->where('transaction_type', 'Party Opening')
            ->selectRaw('COALESCE(SUM(to_pay) - SUM(to_receive), 0) as opening_payable')
            ->where('created_by', 3)
            ->first()
            ->opening_payable ?? 0;

        $partyPaymentReceiveSum = PartyPayment::where('payment_direction', 'receive')
            ->selectRaw('
                (SELECT SUM(amount) FROM party_payments
                WHERE payment_direction = \'receive\'' .
                (!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only') ? ' AND created_by = ' . auth()->user()->id : '') . ')
                -
                COALESCE(
                    (SELECT SUM(payment_transactions.amount)
                    FROM payment_transactions
                    INNER JOIN party_payment_allocations
                        ON payment_transactions.id = party_payment_allocations.payment_transaction_id
                    WHERE party_payment_allocations.party_payment_id IN
                        (SELECT id FROM party_payments
                        WHERE payment_direction = \'receive\'' .
                (!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only') ? ' AND created_by = ' . auth()->user()->id : '') . ')
                    ), 0)
                AS total_amount')
            ->value('total_amount') ?? 0;

        $partyPaymentPaySum = PartyPayment::where('payment_direction', 'pay')
            ->selectRaw('
                (SELECT SUM(amount) FROM party_payments
                WHERE payment_direction = \'pay\'' .
                (!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only') ? ' AND created_by = ' . auth()->user()->id : '') . ')
                -
                COALESCE(
                    (SELECT SUM(payment_transactions.amount)
                    FROM payment_transactions
                    INNER JOIN party_payment_allocations
                        ON payment_transactions.id = party_payment_allocations.payment_transaction_id
                    WHERE party_payment_allocations.party_payment_id IN
                        (SELECT id FROM party_payments
                        WHERE payment_direction = \'pay\'' .
                (!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only') ? ' AND created_by = ' . auth()->user()->id : '') . ')
                    ), 0)
                AS total_amount')
            ->value('total_amount') ?? 0;

        $saleBalance = Sale::selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as total')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        $saleReturnBalance = SaleReturn::selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as total')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        $purchaseBalance = Purchase::selectRaw('COALESCE(SUM((grand_total - shipping_charge) -
                                    CASE
                                        WHEN paid_amount >= (grand_total - shipping_charge)
                                        THEN paid_amount - shipping_charge
                                        ELSE paid_amount
                                    END), 0) as total')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        $purchaseReturnBalance = PurchaseReturn::selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as total')
            ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
            ->value('total');

        $partyReceivable = $openingReceivable + $saleBalance - $saleReturnBalance - $partyPaymentReceiveSum;
        $partyPayable = $openingPayable + $purchaseBalance - $purchaseReturnBalance - $partyPaymentPaySum;

        return [
            'payable' => abs($partyPayable),
            'receivable' => abs($partyReceivable),
        ];
    }

    public function getLowStockItemRecords()
    {
        return Item::with('baseUnit')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderByDesc('current_stock')
            ->limit(10)->get();
    }

    public function refreshData()
    {
        try {
            // Refresh all dashboard data
            $pendingInvoices = Sale::where('invoice_status', 'pending')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $totalCompletedSaleOrders = Sale::where('invoice_status', 'finished')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $totalPaidAmountForFinishedInvoices = Sale::where('invoice_status', 'finished')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->sum('paid_amount');

            $totalSuppliers = Party::where('party_type', 'supplier')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $pendingPurchaseOrders = PurchaseOrder::whereDoesntHave('purchase')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $totalPurchaseOrders = Purchase::when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
                ->count();

            $totalCustomers = Party::where('party_type', 'customer')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                    return $query->where('created_by', auth()->user()->id);
                })
                ->count();

            $totalExpense = PaymentTransaction::where('transaction_type', 'Purchase')
                ->when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($q) {
                    return $q->where('created_by', auth()->user()->id);
                })
                ->sum('amount');

            $partyBalance = $this->paymentReceivables();
            $totalPaymentReceivables = $this->formatWithPrecision($partyBalance['receivable']);

            $saleVsPurchase = $this->saleVsPurchase();
            $trendingItems = $this->trendingItems();
            $lowStockItems = $this->getLowStockItemRecords();
            $recentInvoices = Sale::when(!optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('created_by', auth()->user()->id);
            })
                ->orderByDesc('id')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'pendingInvoices' => $pendingInvoices,
                'totalCompletedSaleOrders' => $totalCompletedSaleOrders,
                'totalPaidAmountForFinishedInvoices' => $totalPaidAmountForFinishedInvoices,
                'totalSuppliers' => $totalSuppliers,
                'pendingPurchaseOrders' => $pendingPurchaseOrders,
                'totalPurchaseOrders' => $totalPurchaseOrders,
                'totalCustomers' => $totalCustomers,
                'totalExpense' => $this->formatWithPrecision($totalExpense),
                'totalPaymentReceivables' => $totalPaymentReceivables,
                'saleVsPurchase' => $saleVsPurchase,
                'trendingItems' => $trendingItems,
                'lowStockItems' => $lowStockItems->map(function($item) {
                    return [
                        'name' => $item->name,
                        'brand' => $item->brand->name ?? '',
                        'category' => $item->category->name,
                        'min_stock' => $this->formatQuantity($item->min_stock),
                        'current_stock' => $this->formatQuantity($item->current_stock),
                        'unit' => $item->baseUnit->name,
                    ];
                }),
                'recentInvoices' => $recentInvoices->map(function($invoice) {
                    return [
                        'formatted_sale_date' => $invoice->formatted_sale_date,
                        'sale_code' => $invoice->sale_code,
                        'party_name' => $invoice->party->getFullName(),
                        'grand_total' => $this->formatWithPrecision($invoice->grand_total),
                        'balance' => $this->formatWithPrecision($invoice->grand_total - $invoice->paid_amount),
                        'status' => $this->getInvoiceStatus($invoice),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh dashboard data'
            ], 500);
        }
    }

    private function getInvoiceStatus($invoice)
    {
        if ($invoice->grand_total == $invoice->paid_amount) {
            return ['class' => 'success', 'message' => 'Paid'];
        } elseif ($invoice->grand_total < $invoice->paid_amount) {
            return ['class' => 'warning', 'message' => 'Partial'];
        } else {
            return ['class' => 'danger', 'message' => 'Unpaid'];
        }
    }
}