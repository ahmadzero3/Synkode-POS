<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Register;
use App\Models\CashAdjustment;
use App\Models\PaymentTransaction;
use App\Services\PaymentTypeService;
use App\Enums\PaymentTypesUniqueCode;

class CloseCashController extends Controller
{
    /**
     * Get all register IDs owned by a user (or empty array if none).
     */
    private function userRegisterIds(int $userId): array
    {
        return Register::where('user_id', $userId)->pluck('id')->all();
    }

    /**
     * Check if user has any registers
     */
    private function userHasRegister(int $userId): bool
    {
        return Register::where('user_id', $userId)->exists();
    }

    /**
     * Apply a "date up to X" filter that is robust when adjustment_date is NULL.
     */
    private function whereEffectiveDateLte($query, string $toDate)
    {
        return $query->where(function ($q) use ($toDate) {
            $q->whereDate('adjustment_date', '<=', $toDate)
                ->orWhere(function ($qq) use ($toDate) {
                    $qq->whereNull('adjustment_date')
                        ->whereDate('created_at', '<=', $toDate);
                });
        });
    }

    /**
     * Apply a "date equals X" filter with the same robustness.
     */
    private function whereEffectiveDateEq($query, string $day)
    {
        return $query->where(function ($q) use ($day) {
            $q->whereDate('adjustment_date', '=', $day)
                ->orWhere(function ($qq) use ($day) {
                    $qq->whereNull('adjustment_date')
                        ->whereDate('created_at', '=', $day);
                });
        });
    }

    /**
     * Opening Balance = (sum Cash Increase) - (sum Cash Reduce) up to AND INCLUDING today
     * For users with registers: ONLY their register data
     * For users without registers: ALL data
     */
    private function openingBalance(?array $registerIds, string $toDate, int $userId): float
    {
        $baseQuery = CashAdjustment::query();

        // If user has registers, restrict to ONLY their registers
        if (!empty($registerIds)) {
            $baseQuery->whereIn('register_id', $registerIds);
        } else {
            // If user has no registers, show ALL data (no filtering)
            // No additional conditions needed
        }

        $baseQuery->where(function ($q) use ($toDate) {
            $q->whereDate('adjustment_date', '<=', $toDate)
                ->orWhere(function ($qq) use ($toDate) {
                    $qq->whereNull('adjustment_date')
                        ->whereDate('created_at', '<=', $toDate);
                });
        });

        // --- Cash Increases ---
        $inc = (clone $baseQuery)
            ->where('adjustment_type', 'Cash Increase')
            ->sum('amount');

        // --- Cash Reductions ---
        $red = (clone $baseQuery)
            ->where('adjustment_type', 'Cash Reduce')
            ->sum('amount');

        return (float) $inc - (float) $red;
    }

    /**
     * Today's income & expenses - EXCLUDES cash adjustments to prevent double-counting
     * For users with registers: ONLY their transactions
     * For users without registers: ALL transactions
     */
    private function todayIncomeExpense(
        ?array $registerIds,
        int $userId,
        string $today,
        PaymentTypeService $paymentTypeService
    ): array {
        $cashId = $paymentTypeService->returnPaymentTypeId(PaymentTypesUniqueCode::CASH->value);

        $basePT = PaymentTransaction::query()
            ->whereDate('transaction_date', $today)
            ->where(function ($q) use ($cashId) {
                $q->where('payment_type_id', $cashId)
                    ->orWhere('transfer_to_payment_type_id', $cashId);
            });

        // If user has registers, restrict to ONLY their transactions
        if (!empty($registerIds)) {
            $basePT->where('created_by', $userId);
        } else {
            // If user has no registers, show ALL transactions (no filtering)
            // No additional conditions needed
        }

        $incomeTypes = ['Sale', 'Purchase Return', 'Sale Order'];
        $expenseTypes = ['Expense', 'Purchase', 'Sale Return', 'Purchase Order'];

        $todayIncome = (float) (clone $basePT)->whereIn('transaction_type', $incomeTypes)->sum('amount');
        $todayExpenses = (float) (clone $basePT)->whereIn('transaction_type', $expenseTypes)->sum('amount');

        return [$todayIncome, $todayExpenses];
    }

    /**
     * PAGE: Close Cash
     */
    public function index(Request $request, PaymentTypeService $paymentTypeService): View
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $registerIds = $this->userRegisterIds($user->id);
        $userHasRegister = !empty($registerIds);
        $filteredUserId = $userHasRegister ? $user->id : null;

        // Opening balance - properly filtered based on user role
        $openingBalance = $this->openingBalance($registerIds, $today, $user->id);

        // Today's income/expenses - properly filtered based on user role
        [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $user->id, $today, $paymentTypeService);

        $totalIncome = $openingBalance + $todayIncome;

        return view('transaction.close-cash', [
            'openingBalance' => $openingBalance,
            'todayIncome' => $todayIncome,
            'todayExpenses' => $todayExpenses,
            'totalIncome' => $totalIncome,
            // helper flags for JS filtering
            'filteredUserId' => $filteredUserId,
            'restrictToUser' => $userHasRegister,
            'userHasRegister' => $userHasRegister,
        ]);
    }

    /**
     * ✅ Alias for backward compatibility with existing routes.
     */
    public function showCloseCashForm(Request $request, PaymentTypeService $paymentTypeService): View
    {
        return $this->index($request, $paymentTypeService);
    }

    public function close(Request $request)
    {
        return response()->json(['status' => 'success', 'message' => 'Cash closed successfully']);
    }

    public function showApplyCloseCashPrint(PaymentTypeService $paymentTypeService)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $registerIds = $this->userRegisterIds($user->id);
        $openingBalance = $this->openingBalance($registerIds, $today, $user->id);
        [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $user->id, $today, $paymentTypeService);

        $totalIncome = $openingBalance + $todayIncome;
        $closingBalance = $totalIncome - $todayExpenses;

        $lastUserId = DB::table('close_cash')->latest()->value('created_by');
        $userName = DB::table('users')->where('id', $lastUserId)->value('username');

        $data = compact('openingBalance', 'todayIncome', 'totalIncome', 'todayExpenses', 'closingBalance', 'userName');

        return view('transaction.apply-close-cash-print.apply-close-cash-print', compact('data'));
    }

    public function printReceipt(PaymentTypeService $paymentTypeService)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $registerIds = $this->userRegisterIds($user->id);
        $openingBalance = $this->openingBalance($registerIds, $today, $user->id);
        [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $user->id, $today, $paymentTypeService);

        $totalIncome = $openingBalance + $todayIncome;
        $closingBalance = $totalIncome - $todayExpenses;

        $data = compact('openingBalance', 'todayIncome', 'totalIncome', 'todayExpenses', 'closingBalance');

        return view('transaction.close-cash-print.close-cash-print', compact('data'));
    }

    public function insertCloseCashData(Request $request)
    {
        try {
            $data = $request->validate([
                'opening_balance' => 'required|numeric',
                'today_income' => 'required|numeric',
                'total_income' => 'required|numeric',
                'today_expenses' => 'required|numeric',
                'balance' => 'required|numeric',
                'created_by' => 'required|exists:users,id',
            ]);

            // Ensure the created_by is always the current user
            $data['created_by'] = auth()->id();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('close_cash')->insert($data);

            return response()->json(['status' => 'success', 'message' => 'Data inserted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while saving the data.'], 500);
        }
    }

    public function listCloseCash()
    {
        // Users can only see their own close cash records
        $closeCashData = DB::table('close_cash')
            ->where('created_by', auth()->id())
            ->get();

        return view('transaction.list-close-cash', compact('closeCashData'));
    }

    public function datatableList(Request $request)
    {
        $query = DB::table('close_cash')
            ->join('users', 'close_cash.created_by', '=', 'users.id')
            ->select([
                'close_cash.id',
                'close_cash.opening_balance',
                'close_cash.today_income',
                'close_cash.total_income',
                'close_cash.today_expenses',
                'close_cash.balance',
                'users.username as created_by',
                'close_cash.created_at',
                'close_cash.updated_at',
            ])
            ->where('close_cash.created_by', auth()->id()); // Users can only see their own records

        if ($request->filled('from_date')) {
            $query->whereDate('close_cash.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('close_cash.created_at', '<=', $request->to_date);
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $actionBtn = '<div class="dropdown ms-auto">';
                $actionBtn .= '<a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">';
                $actionBtn .= '<i class="bx bx-dots-vertical-rounded font-22 text-option"></i>';
                $actionBtn .= '</a>';
                $actionBtn .= '<ul class="dropdown-menu">';

                $actionBtn .= '<a href="' . route('close-cash.edit', $id) . '" class="dropdown-item">'
                    . '<i class="bx bx-edit"></i> ' . __('app.edit') . '</a>';

                $actionBtn .= '<a class="dropdown-item" href="' . route('close-cash.details', $id) . '"><i class="bx bx-show-alt"></i> ' . __('app.details') . '</a>';

                $actionBtn .= '<a target="_blank" class="dropdown-item" href="' . route('close-cash.print', $id) . '"><i class="bx bx-printer"></i> ' . __('app.print') . '</a>';

                $actionBtn .= '<a target="_blank" class="dropdown-item" href="' . route('close-cash.print', ['id' => $id, 'type' => 'pdf']) . '"><i class="bx bxs-file-pdf"></i> ' . __('app.pdf') . '</a>';

                $actionBtn .= '<li>';
                $actionBtn .= '<a class="dropdown-item text-danger deleteRequest" data-delete-id="' . $id . '">'
                    . '<i class="bx bx-trash"></i> ' . __('app.delete') . '</a>';
                $actionBtn .= '</li>';

                $actionBtn .= '</ul>';
                $actionBtn .= '</div>';

                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function delete($id)
    {
        try {
            // Users can only delete their own records
            DB::table('close_cash')->where('id', $id)->where('created_by', auth()->id())->delete();
            return response()->json(['status' => 'success', 'message' => __('app.record_deleted_successfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        // Users can only edit their own records
        $closeCash = DB::table('close_cash')->where('id', $id)->where('created_by', auth()->id())->first();

        if (!$closeCash) {
            abort(404, 'Record not found or access denied');
        }

        return view('transaction.edit-list-close-cash', compact('closeCash'));
    }

    public function update(Request $request, $id)
    {
        // Users can only update their own records
        $closeCash = DB::table('close_cash')->where('id', $id)->where('created_by', auth()->id())->first();

        if (!$closeCash) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Record not found or access denied'], 404);
            }
            return redirect()->route('close.cash.list')->with('error', 'Record not found or access denied');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric',
            'today_income' => 'required|numeric',
            'total_income' => 'required|numeric',
            'today_expenses' => 'required|numeric',
            'balance' => 'required|numeric',
        ]);

        $affected = DB::table('close_cash')
            ->where('id', $id)
            ->where('created_by', auth()->id())
            ->update(array_merge($validated, ['updated_at' => now()]));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $affected ? 'success' : 'error',
                'message' => $affected
                    ? __('app.record_updated_successfully')
                    : __('app.no_records_updated')
            ]);
        }

        return redirect()->route('close.cash.list')
            ->with(
                $affected ? 'success' : 'error',
                $affected ? __('app.record_updated_successfully') : __('app.no_records_updated')
            );
    }

    public function showCloseCashDetails($id)
    {
        // Users can only view their own records
        $closeCash = DB::table('close_cash')
            ->join('users', 'close_cash.created_by', '=', 'users.id')
            ->select('close_cash.*', 'users.username as created_by_name')
            ->where('close_cash.id', $id)
            ->where('close_cash.created_by', auth()->id())
            ->first();

        if (!$closeCash) {
            abort(404, 'Record not found or access denied');
        }

        return view('print.list-close-cash.details', compact('closeCash'));
    }

    public function printCloseCash($id, $type = 'print')
    {
        // Users can only print their own records
        $closeCash = DB::table('close_cash')
            ->join('users', 'close_cash.created_by', '=', 'users.id')
            ->select('close_cash.*', 'users.username as created_by_name')
            ->where('close_cash.id', $id)
            ->where('close_cash.created_by', auth()->id())
            ->first();

        if (!$closeCash) {
            abort(404, 'Record not found or access denied');
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('print.list-close-cash.print-list-close-cash', [
                'closeCash' => $closeCash,
                'formatNumber' => new class() {
                    public function formatWithPrecision($number, $precision = 2)
                    {
                        return number_format($number, $precision);
                    }
                    public function spell($number)
                    {
                        $nf = new \NumberFormatter(config('app.locale', 'en_US'), \NumberFormatter::SPELLOUT);
                        return ucwords($nf->format($number));
                    }
                },
                'appDirection' => config('app.direction', 'ltr'),
                'isPdf' => true
            ]);

            return $pdf->download("close-cash-{$id}.pdf");
        }

        return view('print.list-close-cash.print-list-close-cash', [
            'closeCash' => $closeCash,
            'isPdf' => ($type === 'pdf'),
            'formatNumber' => new class() {
                public function formatWithPrecision($number, $precision = 2)
                {
                    return number_format($number, $precision);
                }
                public function spell($number)
                {
                    $nf = new \NumberFormatter(config('app.locale', 'en_US'), \NumberFormatter::SPELLOUT);
                    return ucwords($nf->format($number));
                }
            },
            'appDirection' => config('app.direction', 'ltr')
        ]);
    }

    /**
     * Generate Report X - Register Shift Report
     */
    public function generateReportX(Request $request, PaymentTypeService $paymentTypeService)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $registerIds = $this->userRegisterIds($user->id);
        $openingBalance = $this->openingBalance($registerIds, $today, $user->id);
        [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $user->id, $today, $paymentTypeService);

        $totalIncome = $openingBalance + $todayIncome;
        $closingBalance = $totalIncome - $todayExpenses;

        $reportData = [
            'report_code' => 'RX-' . now()->format('Ymd-His'),
            'report_date' => now()->format('d-m-Y'),
            'report_time' => now()->format('H:i:s'),
            'cashier_name' => $user->username,
            'register_name' => $user->register->name ?? __('app.main_register'),
            'opening_balance' => $openingBalance,
            'today_income' => $todayIncome,
            'total_income' => $totalIncome,
            'today_expenses' => $todayExpenses,
            'balance_cash' => $totalIncome - $todayExpenses,
            'closing_balance' => $closingBalance,
            'transactions' => $this->getRegisterTransactions($user->id, $today)
        ];

        return view('print.close-cash.report-x-print', [
            'reportData' => $reportData,
            'isPdf' => false,
            'appDirection' => config('app.direction', 'ltr')
        ]);
    }

    /**
     * Generate Report X PDF
     */
    public function generateReportXPdf(Request $request, PaymentTypeService $paymentTypeService)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $registerIds = $this->userRegisterIds($user->id);
        $openingBalance = $this->openingBalance($registerIds, $today, $user->id);
        [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $user->id, $today, $paymentTypeService);

        $totalIncome = $openingBalance + $todayIncome;
        $closingBalance = $totalIncome - $todayExpenses;

        $reportData = [
            'report_code' => 'RX-' . now()->format('Ymd-His'),
            'report_date' => now()->format('d-m-Y'),
            'report_time' => now()->format('H:i:s'),
            'cashier_name' => $user->username,
            'register_name' => $user->register->name ?? __('app.main_register'),
            'opening_balance' => $openingBalance,
            'today_income' => $todayIncome,
            'total_income' => $totalIncome,
            'today_expenses' => $todayExpenses,
            'balance_cash' => $totalIncome - $todayExpenses,
            'closing_balance' => $closingBalance,
            'transactions' => $this->getRegisterTransactions($user->id, $today)
        ];

        $pdf = Pdf::loadView('print.close-cash.report-x-print', [
            'reportData' => $reportData,
            'isPdf' => true,
            'appDirection' => config('app.direction', 'ltr')
        ]);

        return $pdf->download("report-x-" . now()->format('Y-m-d') . ".pdf");
    }

    /**
     * Generate Report Z - End of Day Report
     */
    public function generateReportZ(Request $request, PaymentTypeService $paymentTypeService)
    {
        $today = now()->toDateString();

        $reportData = [
            'report_code' => 'RZ-' . now()->format('Ymd'),
            'report_date' => now()->format('d-m-Y'),
            'report_time' => now()->format('H:i:s'),
            'business_date' => $today,
            'total_opening_balance' => $this->getTotalOpeningBalance($today),
            'total_daily_income' => $this->getTotalDailyIncome($today, $paymentTypeService),
            'grand_total_income' => $this->getGrandTotalIncome($today, $paymentTypeService),
            'total_daily_expenses' => $this->getTotalDailyExpenses($today, $paymentTypeService),
            'net_cash_position' => $this->getNetCashPosition($today, $paymentTypeService),
            'final_closing_balance' => $this->getFinalClosingBalance($today, $paymentTypeService),
            'register_summaries' => $this->getRegisterSummaries($today, $paymentTypeService),
            'payment_type_summary' => $this->getPaymentTypeSummary($today)
        ];

        return view('print.close-cash.report-z-print', [
            'reportData' => $reportData,
            'isPdf' => false,
            'appDirection' => config('app.direction', 'ltr')
        ]);
    }

    /**
     * Generate Report Z PDF
     */
    public function generateReportZPdf(Request $request, PaymentTypeService $paymentTypeService)
    {
        $today = now()->toDateString();

        $reportData = [
            'report_code' => 'RZ-' . now()->format('Ymd-His'),
            'report_date' => now()->format('d-m-Y'),
            'report_time' => now()->format('H:i:s'),
            'business_date' => $today,
            'total_opening_balance' => $this->getTotalOpeningBalance($today),
            'total_daily_income' => $this->getTotalDailyIncome($today, $paymentTypeService),
            'grand_total_income' => $this->getGrandTotalIncome($today, $paymentTypeService),
            'total_daily_expenses' => $this->getTotalDailyExpenses($today, $paymentTypeService),
            'net_cash_position' => $this->getNetCashPosition($today, $paymentTypeService),
            'final_closing_balance' => $this->getFinalClosingBalance($today, $paymentTypeService),
            'register_summaries' => $this->getRegisterSummaries($today, $paymentTypeService),
            'payment_type_summary' => $this->getPaymentTypeSummary($today)
        ];

        $pdf = Pdf::loadView('print.close-cash.report-z-print', [
            'reportData' => $reportData,
            'isPdf' => true,
            'appDirection' => config('app.direction', 'ltr')
        ]);

        return $pdf->download("report-z-" . now()->format('Y-m-d') . ".pdf");
    }

    // Helper methods for Report Z
    private function getTotalOpeningBalance($date)
    {
        // Get total opening balance for all registers
        $allRegisterIds = Register::pluck('id')->all();
        return $this->openingBalance($allRegisterIds, $date, auth()->id());
    }

    private function getTotalDailyIncome($date, PaymentTypeService $paymentTypeService)
    {
        // Get total daily income for all registers
        $cashId = $paymentTypeService->returnPaymentTypeId(PaymentTypesUniqueCode::CASH->value);

        $incomeTypes = ['Sale', 'Purchase Return', 'Sale Order'];

        $totalIncome = PaymentTransaction::query()
            ->whereDate('transaction_date', $date)
            ->where(function ($q) use ($cashId) {
                $q->where('payment_type_id', $cashId)
                    ->orWhere('transfer_to_payment_type_id', $cashId);
            })
            ->whereIn('transaction_type', $incomeTypes)
            ->sum('amount');

        return $totalIncome;
    }

    private function getGrandTotalIncome($date, PaymentTypeService $paymentTypeService)
    {
        $totalOpening = $this->getTotalOpeningBalance($date);
        $totalDailyIncome = $this->getTotalDailyIncome($date, $paymentTypeService);
        return $totalOpening + $totalDailyIncome;
    }

    private function getTotalDailyExpenses($date, PaymentTypeService $paymentTypeService)
    {
        // Get total daily expenses for all registers
        $cashId = $paymentTypeService->returnPaymentTypeId(PaymentTypesUniqueCode::CASH->value);

        $expenseTypes = ['Expense', 'Purchase', 'Sale Return', 'Purchase Order'];

        $totalExpenses = PaymentTransaction::query()
            ->whereDate('transaction_date', $date)
            ->where(function ($q) use ($cashId) {
                $q->where('payment_type_id', $cashId)
                    ->orWhere('transfer_to_payment_type_id', $cashId);
            })
            ->whereIn('transaction_type', $expenseTypes)
            ->sum('amount');

        return $totalExpenses;
    }

    private function getNetCashPosition($date, PaymentTypeService $paymentTypeService)
    {
        $grandTotalIncome = $this->getGrandTotalIncome($date, $paymentTypeService);
        $totalExpenses = $this->getTotalDailyExpenses($date, $paymentTypeService);
        return $grandTotalIncome - $totalExpenses;
    }

    private function getFinalClosingBalance($date, PaymentTypeService $paymentTypeService)
    {
        return $this->getNetCashPosition($date, $paymentTypeService);
    }

    private function getRegisterSummaries($date, PaymentTypeService $paymentTypeService)
    {
        $registers = Register::with('user')->get();
        $summaries = [];

        foreach ($registers as $register) {
            $registerIds = [$register->id];
            $userId = $register->user_id;

            $openingBalance = $this->openingBalance($registerIds, $date, $userId);
            [$todayIncome, $todayExpenses] = $this->todayIncomeExpense($registerIds, $userId, $date, $paymentTypeService);

            $totalIncome = $openingBalance + $todayIncome;
            $closingBalance = $totalIncome - $todayExpenses;

            $summaries[] = [
                'register_name' => $register->name,
                'cashier_name' => $register->user->username ?? 'N/A',
                'opening_balance' => $openingBalance,
                'today_income' => $todayIncome,
                'today_expenses' => $todayExpenses,
                'closing_balance' => $closingBalance
            ];
        }

        return $summaries;
    }

    private function getPaymentTypeSummary($date)
    {
        $paymentTypes = DB::table('payment_types')
            ->where('status', true)
            ->get();

        $summary = [];

        foreach ($paymentTypes as $paymentType) {
            $totalAmount = PaymentTransaction::whereDate('transaction_date', $date)
                ->where('payment_type_id', $paymentType->id)
                ->sum('amount');

            if ($totalAmount > 0) {
                $summary[] = [
                    'type' => $paymentType->name,
                    'amount' => $totalAmount,
                    'percentage' => 0 // Will be calculated based on grand total
                ];
            }
        }

        // Calculate percentages
        $grandTotal = array_sum(array_column($summary, 'amount'));
        if ($grandTotal > 0) {
            foreach ($summary as &$item) {
                $item['percentage'] = ($item['amount'] / $grandTotal) * 100;
            }
        }

        return $summary;
    }

    private function getRegisterTransactions($userId, $date)
    {
        $cashId = app(PaymentTypeService::class)->returnPaymentTypeId(PaymentTypesUniqueCode::CASH->value);

        $transactions = PaymentTransaction::with(['transaction', 'paymentType'])
            ->where('created_by', $userId)
            ->whereDate('transaction_date', $date)
            ->where(function ($q) use ($cashId) {
                $q->where('payment_type_id', $cashId)
                    ->orWhere('transfer_to_payment_type_id', $cashId);
            })
            ->orderBy('transaction_date', 'asc')
            ->get();

        $formattedTransactions = [];

        foreach ($transactions as $transaction) {
            $description = $transaction->transaction_type;
            if ($transaction->transaction) {
                if (method_exists($transaction->transaction, 'getTableCode')) {
                    $description .= ' - ' . $transaction->transaction->getTableCode();
                }
            }

            $formattedTransactions[] = [
                'type' => $transaction->transaction_type,
                'description' => $description,
                'amount' => $transaction->amount,
                'time' => $transaction->transaction_date->format('H:i:s')
            ];
        }

        return $formattedTransactions;
    }
}
