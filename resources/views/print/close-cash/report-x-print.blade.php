<!DOCTYPE html>
<html lang="ar" dir="{{ $appDirection }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('payment.report_x_register_shift') }}</title>
    @include('print.common.css')
</head>
<body onload="window.print();">
    <div class="invoice-container">
        <span class="invoice-name">{{ __('payment.report_x_register_shift') }}</span>
        <div class="invoice">
            <table class="header">
                <tr>
                    @include('print.common.header')

                    <td class="bill-info">
                        <span class="bill-number"># {{ $reportData['report_code'] ?? 'RX-' . now()->format('Ymd-His') }}</span><br>
                        <span class="cu-fs-16">{{ __('app.date') }}: {{ $reportData['report_date'] ?? now()->format('d-m-Y') }}</span><br>
                        <span class="cu-fs-16">{{ __('app.time') }}: {{ $reportData['report_time'] ?? now()->format('H:i:s') }}</span><br>
                        @if($reportData['cashier_name'] ?? false)
                        <span class="cu-fs-16">{{ __('app.cashier') }}: {{ $reportData['cashier_name'] }}</span><br>
                        @endif
                        @if($reportData['register_name'] ?? false)
                        <span class="cu-fs-16">{{ __('register.registers') }}: {{ $reportData['register_name'] }}</span><br>
                        @endif
                    </td>
                </tr>
            </table>

            <table class="table-bordered custom-table table-compact" id="report-table">
                <thead>
                    <tr>
                        <th>{{ __('app.description') }}</th>
                        <th>{{ __('payment.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left"><b>{{ __('payment.opening_balance') }}</b></td>
                        <td class="text-end">{{ number_format($reportData['opening_balance'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-left"><b>{{ __('payment.today_income') }}</b></td>
                        <td class="text-end">{{ number_format($reportData['today_income'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-green">
                        <td class="text-left"><b>{{ __('payment.total_income') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['total_income'] ?? 0, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td class="text-left"><b>{{ __('payment.today_expense') }}</b></td>
                        <td class="text-end">{{ number_format($reportData['today_expenses'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-blue">
                        <td class="text-left"><b>{{ __('payment.balance_cash_in_hand') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['balance_cash'] ?? 0, 2) }}</b></td>
                    </tr>
                    <tr class="bg-yellow">
                        <td class="text-left"><b>{{ __('payment.today_closing_balance') }}</b></td>
                        <td class="text-end"><b>{{ number_format($reportData['closing_balance'] ?? 0, 2) }}</b></td>
                    </tr>
                </tbody>
            </table>

            @if(isset($reportData['transactions']) && count($reportData['transactions']) > 0)
            <div style="margin-top: 20px;">
                <h4>{{ __('app.transactions') }}</h4>
                <table class="table-bordered custom-table table-compact" id="transactions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('app.type') }}</th>
                            <th>{{ __('app.description') }}</th>
                            <th>{{ __('payment.amount') }}</th>
                            <th>{{ __('app.time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['transactions'] as $index => $transaction)
                        <tr>
                            <td class="no">{{ $index + 1 }}</td>
                            <td>{{ $transaction['type'] ?? '' }}</td>
                            <td class="text-left">{{ $transaction['description'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($transaction['amount'] ?? 0, 2) }}</td>
                            <td>{{ $transaction['time'] ?? '' }}</td>
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
                {{ __('payment.report_x_register_shift_note') }}
            </div>
        </div>
    </div>
</body>
</html>