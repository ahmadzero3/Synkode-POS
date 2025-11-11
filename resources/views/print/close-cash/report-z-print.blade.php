<!DOCTYPE html>
<html lang="ar" dir="{{ $appDirection }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('payment.report_z_end_day') }}</title>
    @include('print.common.css')
</head>
<body onload="window.print();">
    <div class="invoice-container">
        <span class="invoice-name">{{ __('payment.report_z_end_day') }}</span>
        <div class="invoice">
            <table class="header">
                <tr>
                    @include('print.common.header')

                    <td class="bill-info">
                        <span class="bill-number"># {{ $reportData['report_code'] ?? 'RZ-' . now()->format('Ymd-His') }}</span><br>
                        <span class="cu-fs-16">{{ __('app.date') }}: {{ $reportData['report_date'] ?? now()->format('d-m-Y') }}</span><br>
                        <span class="cu-fs-16">{{ __('app.time') }}: {{ $reportData['report_time'] ?? now()->format('H:i:s') }}</span><br>
                        <span class="cu-fs-16">{{ __('app.business_day') }}: {{ $reportData['business_date'] ?? now()->format('d-m-Y') }}</span><br>
                    </td>
                </tr>
            </table>

            <table class="table-bordered custom-table table-compact" id="summary-table">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center bg-dark text-white">
                            <h4 class="mb-0">{{ __('payment.end_of_day_summary') }}</h4>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left w-50"><b>{{ __('payment.total_opening_balance') }}</b></td>
                        <td class="text-end w-50">{{ number_format($reportData['total_opening_balance'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-left"><b>{{ __('payment.total_daily_income') }}</b></td>
                        <td class="text-end">{{ number_format($reportData['total_daily_income'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-green">
                        <td class="text-left"><b>{{ __('payment.grand_total_income') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['grand_total_income'] ?? 0, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td class="text-left"><b>{{ __('payment.total_daily_expenses') }}</b></td>
                        <td class="text-end">{{ number_format($reportData['total_daily_expenses'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-blue">
                        <td class="text-left"><b>{{ __('payment.net_cash_position') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['net_cash_position'] ?? 0, 2) }}</b></td>
                    </tr>
                    <tr class="bg-yellow">
                        <td class="text-left"><b>{{ __('payment.final_closing_balance') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['final_closing_balance'] ?? 0, 2) }}</b></td>
                    </tr>
                </tbody>
            </table>

            @if(isset($reportData['register_summaries']) && count($reportData['register_summaries']) > 0)
            <div style="margin-top: 20px;">
                <h4>{{ __('payment.register_wise_summary') }}</h4>
                <table class="table-bordered custom-table table-compact" id="register-summary-table">
                    <thead>
                        <tr>
                            <th>{{ __('register.register') }}</th>
                            <th>{{ __('app.cashier') }}</th>
                            <th>{{ __('payment.opening_balance') }}</th>
                            <th>{{ __('payment.today_income') }}</th>
                            <th>{{ __('payment.today_expense') }}</th>
                            <th>{{ __('payment.closing_balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['register_summaries'] as $summary)
                        <tr>
                            <td>{{ $summary['register_name'] ?? '' }}</td>
                            <td>{{ $summary['cashier_name'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($summary['opening_balance'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($summary['today_income'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($summary['today_expenses'] ?? 0, 2) }}</td>
                            <td class="text-end"><b>{{ number_format($summary['closing_balance'] ?? 0, 2) }}</b></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(isset($reportData['payment_type_summary']) && count($reportData['payment_type_summary']) > 0)
            <div style="margin-top: 20px;">
                <h4>{{ __('payment.payment_type_breakdown') }}</h4>
                <table class="table-bordered custom-table table-compact" id="payment-type-table">
                    <thead>
                        <tr>
                            <th>{{ __('payment.payment_type') }}</th>
                            <th>{{ __('payment.total_amount') }}</th>
                            <th>{{ __('app.percentage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['payment_type_summary'] as $payment)
                        <tr>
                            <td>{{ $payment['type'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($payment['percentage'] ?? 0, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div style="margin-top: 30px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; text-align: center; padding: 20px;">
                            <div style="border-top: 1px solid #000; padding-top: 10px;">
                                {{ __('app.cashier_signature') }}
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; padding: 20px;">
                            <div style="border-top: 1px solid #000; padding-top: 10px;">
                                {{ __('app.manager_signature') }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
                {{ __('payment.report_z_end_day_note') }}
            </div>
        </div>
    </div>
</body>
</html>