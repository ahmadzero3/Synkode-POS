<!DOCTYPE html>
<html lang="en" dir="{{ $appDirection }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoiceData['name'] }}</title>
    <!--favicon-->
    <link rel="icon" href='{{ url('/fevicon/' . $fevicon) }}' type="image/png" />
    <link href="{{ versionedAsset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ versionedAsset('custom/css/print/pos.css') }}" />
</head>

<body>
    <div class="invoice-wrapper">
        <div class="container mt-3">
            <div class="invoice-header">
                <div class="logo-container">
                    <img src="{{ url('/fevicon/' . $fevicon) }}" alt="Logo" class="invoice-logo">
                </div>
                <div class="invoice-title">{{ app('company')['name'] }}</div>
                <div>
                    {{ app('company')['address'] }}
                    <p>
                        @if (app('company')['mobile'] || app('company')['email'])
                            {{ app('company')['mobile'] ? 'M: ' . app('company')['mobile'] : '' }}{{ app('company')['email'] ? ', Mail: ' . app('company')['email'] : '' }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="text-center">--------{{ $invoiceData['name'] }}--------</div>

            <div class="row">
                <div class="col-6">
                    <div>{{ __('app.name') }}: {{ $sale->party->first_name . ' ' . $sale->party->last_name }}</div>
                    <div>{{ __('app.mobile') }}: {{ $sale->party->mobile }}</div>
                    <div>{{ __('sale.seller') }}: {{ $sale->user->first_name . ' ' . $sale->user->last_name }}</div>
                </div>
                <div class="col-6 text-end">
                    <div>{{ __('sale.invoice') }}: #{{ $sale->sale_code }}</div>
                    <div>{{ __('app.date') }}: {{ $sale->formatted_sale_date }}</div>
                    <div>{{ __('app.time') }}: {{ $sale->format_created_time }}</div>
                </div>
            </div>

            <table class="table table-sm mt-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.description') }}</th>
                        <th class="text-end">{{ __('app.price_per_unit') }}</th>
                        <th class="text-end">{{ __('app.qty') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp

                    @foreach ($sale->itemTransaction as $transaction)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>
                                {{ $transaction->item->name }}
                                <small>{{ $transaction->description }}</small>
                                <small>
                                    @if ($transaction->itemSerialTransaction->count() > 0)
                                        <br>{{ $transaction->itemSerialTransaction->pluck('itemSerialMaster.serial_code')->implode(',') }}<br>
                                    @endif
                                    @if ($transaction->batch)
                                        <br>
                                        <i>{{ __('item.batch') }}</i>
                                        {{ $transaction->batch->itemBatchMaster->batch_no }}
                                        <!-- Check is expire_date exist then show -->
                                        @if ($transaction->batch->itemBatchMaster->exp_date)
                                            ,<i>{{ __('item.exp') }}</i>
                                            {{ $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->exp_date) }}
                                        @endif
                                    @endif

                                </small>
                            </td>
                            <td class="text-end">
                                {{ $formatNumber->formatWithPrecision($transaction->total / $transaction->quantity) }}
                            </td>
                            <td class="text-end">{{ $formatNumber->formatQuantity($transaction->quantity) }}</td>
                            <td class="text-end">{{ $formatNumber->formatWithPrecision($transaction->total) }}</td>
                        </tr>
                    @endforeach

                    @php
                        $totalQty = $sale->itemTransaction->sum(function ($transaction) {
                            return $transaction->quantity;
                        });
                    @endphp
                    <tr class="text-end fw-bold">
                        <td colspan="3">{{ __('app.total') }}</td>
                        <td>{{ $formatNumber->formatQuantity($totalQty) }}</td>
                        <td>{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="row text-end">
                @php
                    // --- Direct Fetch for Print View ---
                    // Fetch the company data directly here to avoid potential cache issues
                    // Assumes $sale has a company_id attribute or relationship.
                    $companyId = $sale->company_id ?? 1; // Adjust how you get the company ID if different

                    $currentCompanyData = \App\Models\Company::find($companyId)?->toArray(); // Convert to array for easy access

                    // Fallback if direct fetch fails or ID is wrong, try the standard app() helper
                    if (!$currentCompanyData) {
                        $currentCompanyData = app('company');
                    }

                    // Ensure default values if keys are somehow still missing after direct fetch
                    $showDiscountPrint = $currentCompanyData['enable_print_discount'] ?? true; // Default to true if missing
                    $showTaxPrint = !($currentCompanyData['enable_print_tax'] ?? true); // Default to true if missing
                    // --- End Direct Fetch ---

                    $subtotal = $sale->itemTransaction->sum(function ($transaction) {
                        $unitPrice = (float) $transaction->unit_price;
                        $quantity = (float) $transaction->quantity;
                        return $unitPrice * $quantity;
                    });

                    $discount = $sale->itemTransaction->sum(function ($transaction) {
                        return (float) $transaction->discount_amount;
                    });

                    $taxAmount = $sale->itemTransaction->sum(function ($transaction) {
                        return (float) $transaction->tax_amount;
                    });

                @endphp

                <!-- Conditionally display Discount based on BOTH general setting AND print setting -->
                <!-- Use the locally fetched/ensured data -->
                @if (app('company')['show_discount'] && $showDiscountPrint)
                    <div class="col-8 text-end"><strong>{{ __('app.discount') }}</strong></div>
                    <div class="col-4">{{ $formatNumber->formatWithPrecision(-$discount) }}</div>
                @endif

                <!-- Conditionally display Tax based on BOTH tax type setting AND print setting -->
                <!-- Use the locally fetched/ensured data -->
                @if (app('company')['tax_type'] != 'no-tax' && !$showTaxPrint)
                    <div class="col-8 text-end"><strong>{{ __('tax.tax') }}</strong></div>
                    <div class="col-4">{{ $formatNumber->formatWithPrecision($taxAmount) }}</div>
                @endif

                <div class="col-8 text-end"><strong>{{ __('app.total_dollar') }}</strong></div>
                <div class="col-4">{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</div>

                @if ($sale->exchange_rate > 0)
                    <div class="col-8 text-end"><strong>{{ __('app.total_lebanon') }}</strong></div>
                    <div class="col-4">
                        {{ $formatNumber->formatWithPrecision($sale->grand_total * $sale->exchange_rate) }}</div>
                @endif

                <!-- Conditionally display payment type, balance, and change return if they differ from paid amount -->
                @if ($sale->payment_type && $sale->payment_amount != $sale->paid_amount)
                    <div class="col-8 text-end"><strong>{{ __('payment.payment_type') }}</strong></div>
                    <div class="col-4">{{ $sale->payment_type }}</div>
                @endif

                @if ($sale->balance > 0)
                    <div class="col-8 text-end"><strong>{{ __('payment.balance') }}</strong></div>
                    <div class="col-4">{{ $formatNumber->formatWithPrecision($sale->balance) }}</div>
                @endif

                @if ($sale->change_return > 0)
                    <div class="col-8 text-end"><strong>{{ __('payment.payment_amount') }}</strong></div>
                    <div class="col-4">{{ $formatNumber->formatWithPrecision($sale->payment_amount) }}</div>

                    <div class="col-8 text-end"><strong>{{ __('payment.change_return') }}</strong></div>
                    <div class="col-4">{{ $formatNumber->formatWithPrecision($sale->change_return) }}</div>
                @endif

                <div class="col-8 text-end"><strong>{{ __('payment.paid_amount') }}</strong></div>


                @php
                    $paidAmount = $sale->paymentTransaction->sum('amount');
                @endphp


                <div class="col-4">{{ $formatNumber->formatWithPrecision($paidAmount) }}</div>

                @if (app('company')['show_mrp'])
                    @php
                        $savedAmount = $sale->itemTransaction->sum(function ($transaction) {
                            if ($transaction->mrp > 0) {
                                return $transaction->mrp * $transaction->quantity - $transaction->total;
                            } else {
                                return 0;
                            }
                        });

                    @endphp
                @endif

                @if (app('company')['show_party_due_payment'])
                    @php
                        $partyTotalDue = $sale->party->getPartyTotalDueBalance();
                        $partyTotalDueBalance = $partyTotalDue['balance'];
                    @endphp
                @endif


            </div>

            @if (app('company')['show_tax_summary'] && app('company')['tax_type'] != 'no-tax' && !$showTaxPrint)
                <table class="table table-bordered custom-table tax-breakdown table-compact">
                    <thead>
                        @if (app('company')['tax_type'] == 'tax')
                            <tr>
                                <th>{{ __('tax.tax') }}</th>
                                <th>{{ __('tax.taxable_amount') }}</th>
                                <th>{{ __('tax.rate') }}</th>
                                <th>{{ __('tax.tax_amount') }}</th>
                            </tr>
                        @else
                            {{-- GST --}}
                            <tr>
                                <th rowspan="2">{{ __('item.hsn') }}</th>
                                <th rowspan="2">{{ __('tax.taxable_amount') }}</th>
                                <th colspan="2" class="text-center">{{ __('tax.gst') }}</th>
                                <th rowspan="2">{{ __('tax.tax_amount') }}</th>
                            </tr>
                            <tr>
                                <th>{{ __('tax.rate') }}%</th>
                                <th>{{ __('app.amount') }}</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @php

                            if (app('company')['tax_type'] == 'tax') {
                                $taxSummary = $sale->itemTransaction
                                    ->groupBy('tax_id')
                                    ->map(function ($group) {
                                        $firstItem = $group->first();
                                        $totalTaxableAmount = $group->sum(function ($item) use ($firstItem) {
                                            $totalOfEachItem =
                                                ($item->unit_price - $item->discount_amount) * $item->quantity;
                                            return $totalOfEachItem;
                                        });
                                        return [
                                            'tax_id' => $firstItem->tax_id,
                                            'tax_name' => optional($firstItem->tax)->name ?? __('tax.no_tax'),
                                            'tax_rate' => optional($firstItem->tax)->rate ?? 0,
                                            'total_taxable_amount' => $totalTaxableAmount,
                                            'total_tax' => $group->sum('tax_amount'),
                                        ];
                                    })
                                    ->values();
                            } else {
                                //GST
                                $taxSummary = $sale->itemTransaction
                                    ->groupBy('item.hsn') // First group by HSN
                                    ->map(function ($hsnGroup) {
                                        return $hsnGroup
                                            ->groupBy('tax_id') // Then group by tax_id within each HSN group
                                            ->map(function ($group) {
                                                $firstItem = $group->first();
                                                $totalTaxableAmount = $group->sum(function ($item) {
                                                    $totalOfEachItem =
                                                        ($item->unit_price - $item->discount_amount) * $item->quantity;
                                                    return $totalOfEachItem;
                                                });
                                                return [
                                                    'hsn' => $firstItem->item->hsn ?? '-',
                                                    'tax_id' => $firstItem->tax_id,
                                                    'tax_name' => optional($firstItem->tax)->name ?? __('tax.no_tax'),
                                                    'tax_rate' => optional($firstItem->tax)->rate ?? 0,
                                                    'total_taxable_amount' => $totalTaxableAmount,
                                                    'total_tax' => $group->sum('tax_amount'),
                                                ];
                                            });
                                    })
                                    ->flatMap(function ($hsnGroup) {
                                        return $hsnGroup;
                                    })
                                    ->values();
                            }
                        @endphp
                        @foreach ($taxSummary as $summary)
                            @if (app('company')['tax_type'] == 'tax')
                                <tr>
                                    <td>{{ $summary['tax_name'] }}</td>
                                    <td class="text-end">
                                        {{ $formatNumber->formatWithPrecision($summary['total_taxable_amount']) }}</td>
                                    <td class="text-center">{{ $summary['tax_rate'] }}%</td>
                                    <td class="text-end">
                                        {{ $formatNumber->formatWithPrecision($summary['total_tax']) }}</td>
                                </tr>
                            @else
                                <tr>
                                    @php
                                        $isCSGST =
                                            empty($sale->state_id) || app('company')['state_id'] == $sale->state_id
                                                ? true
                                                : false;
                                    @endphp
                                    <td>{{ $summary['hsn'] }}</td>
                                    <td class="text-end">
                                        {{ $formatNumber->formatWithPrecision($summary['total_taxable_amount']) }}</td>

                                    @php
                                        $cs_gst = $i_gst = '';
                                        $cs_gst_amt = $i_gst_amt = '';
                                        if ($isCSGST) {
                                            $cs_gst = $summary['tax_rate'] / 2 . '%';
                                            $cs_gst_amt = $formatNumber->formatWithPrecision($summary['total_tax'] / 2);
                                        } else {
                                            $i_gst = $summary['tax_rate'] . '%';
                                            $i_gst_amt = $formatNumber->formatWithPrecision($summary['total_tax']);
                                        }
                                    @endphp
                                    @if ($isCSGST)
                                        <!-- CGST & SGT -->
                                        <td class="text-center">{{ $cs_gst }}</td>
                                        <td class="text-end">{{ $cs_gst_amt }}</td>
                                    @else
                                        <!-- IGST -->
                                        <td class="text-center">{{ $i_gst }}</td>
                                        <td class="text-end">{{ $i_gst_amt }}</td>
                                    @endif
                                    <td class="text-end">
                                        {{ $formatNumber->formatWithPrecision($summary['total_tax']) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="footer">
                @include('print.common.terms-conditions')
            </div>

            <!-- <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=YourQRCodeDataHere" alt="QR Code">
            </div> -->

        </div>
    </div>
    <div class="container mt-3 mb-3 no-print">
        <button class="btn btn-success print-btn" onclick="window.print()">Print</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ versionedAsset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/js/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            window.print();
        });
    </script>
</body>

</html>
