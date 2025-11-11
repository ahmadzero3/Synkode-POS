<!DOCTYPE html>
<html lang="ar" dir="{{ $appDirection }}">

<head>
    <meta charset="UTF-8">
    <title>{{ $invoiceData['name'] }}</title>
    @include('print.common.css')
</head>

<body onload="window.print();">
    <div class="invoice-container">
        <span class="invoice-name">{{ $invoiceData['name'] }}</span>
        <div class="invoice">
            <table class="header">
                <tr>
                    @include('print.common.header')

                    <td class="bill-info">
                        <span class="bill-number">{{ __('app.bill') }} #: {{ $sale->sale_code }}</span><br>
                        <span class="cu-fs-16">{{ __('app.date') }}: {{ $sale->formatted_sale_date }}</span><br>
                        <span class="cu-fs-16">{{ __('app.time') }}: {{ $sale->format_created_time }}</span><br>
                        @if($sale->reference_no)
                        <span class="cu-fs-16">{{ __('supplier.supplier_invoice_number') }}: {{ $sale->reference_no  }}</span><br>
                        @endif
                        @if($sale->sale_order_id)
                        <span class="cu-fs-16">{{ __('sale.order.order') }}: {{ $sale->saleOrder->order_code  }}</span><br>
                        @endif
                    </td>
                </tr>
            </table>
            <table class="addresses">
                <tr>
                    <td class="address">
                        <span class="fw-bold cu-fs-18">{{ __('app.bill_to') }}</span><br>
                        <span>{{ $sale->party->first_name.' '. $sale->party->last_name }}<br>
                            {{ $sale->party->billing_address }}</span>
                    </td>
                    <td class="address">
                        <span class="fw-bold cu-fs-18">{{ __('app.ship_to') }}</span><br>
                        <span>{{ $sale->party->shipping_address }}</span>
                    </td>
                </tr>
            </table>


            @php
            $isHasBatchItem = ($sale->itemTransaction->where('tracking_type', 'batch')->count() > 0) ? true : false;

            //Return from Controller
            $totalBatchTrackingRowCount = ($isHasBatchItem) ? $batchTrackingRowCount : 0;
            @endphp
            <table class="table-bordered custom-table table-compact" id="item-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('item.item') }}</th>
                        @if(app('company')['show_hsn'])
                        <th>{{ __('item.hsn') }}</th>
                        @endif
                        @if($isHasBatchItem)
                        @if(app('company')['enable_batch_tracking'])
                        <th>{{ __('item.batch_no') }}</th>
                        @endif
                        @if(app('company')['enable_mfg_date'])
                        <th>{{ __('item.mfg_date') }}</th>
                        @endif
                        @if(app('company')['enable_exp_date'])
                        <th>{{ __('item.exp_date') }}</th>
                        @endif
                        @if(app('company')['enable_model'])
                        <th>{{ __('item.model_no') }}</th>
                        @endif

                        @if(app('company')['enable_color'])
                        <th>{{ __('item.color') }}</th>
                        @endif
                        @if(app('company')['enable_size'])
                        <th>{{ __('item.size') }}</th>
                        @endif
                        @endif
                        @if(app('company')['show_mrp'])
                        <th>{{ __('item.mrp') }}</th>
                        @endif
                        <th>{{ __('app.qty') }}</th>
                        <th>{{ __('app.price_per_unit') }}</th>
                        @if(app('company')['show_discount'])
                        <th>{{ __('app.discount') }}</th>
                        @endif
                        @if(app('company')['tax_type'] != 'no-tax')
                        <th>{{ __('tax.tax') }}</th>
                        @endif
                        <th>{{ __('app.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $i=1;
                    @endphp

                    @foreach($sale->itemTransaction as $transaction)
                    <tr>
                        <td class="no">{{ $i++ }}</td>
                        <td class="">
                            <!-- Service Name -->
                            <b>{{ $transaction->item->name }}</b>
                            <!-- Description -->
                            <small>{{ $transaction->description }}</small>
                            <small>
                                @if ($transaction->itemSerialTransaction->count() > 0)
                                <br>{{ $transaction->itemSerialTransaction->pluck('itemSerialMaster.serial_code')->implode(',') }}<br>
                                @endif
                            </small>
                        </td>
                        @if(app('company')['show_hsn'])
                        <td>
                            {{ $transaction->item->hsn }}
                        </td>
                        @endif
                        @if($isHasBatchItem)

                        @if(app('company')['enable_batch_tracking'])
                        <td>
                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->batch_no : '' }}
                        </td>
                        @endif
                        @if(app('company')['enable_mfg_date'])
                        <td>
                            {{ $transaction->batch ? $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->mfg_date) : '' }}
                        </td>
                        @endif
                        @if(app('company')['enable_exp_date'])
                        <td>
                            {{ $transaction->batch ? $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->exp_date) : '' }}
                        </td>
                        @endif
                        @if(app('company')['enable_model'])
                        <td>
                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->model_no : ''}}
                        </td>
                        @endif

                        @if(app('company')['enable_color'])
                        <td>
                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->color :'' }}
                        </td>
                        @endif
                        @if(app('company')['enable_size'])
                        <td>
                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->size : '' }}
                        </td>
                        @endif
                        @endif
                        @if(app('company')['show_mrp'])
                        <td>
                            {{ $formatNumber->formatWithPrecision($transaction->batch ? $transaction->batch->itemBatchMaster->mrp : $transaction->mrp)}}
                        </td>
                        @endif
                        <td class="text-end">
                            {{ $formatNumber->formatQuantity($transaction->quantity) }}
                        </td>
                        <td class=" text-end">
                            {{ $formatNumber->formatWithPrecision($transaction->unit_price) }}<br>
                            <small>{{ $transaction->unit->name }}</small>
                        </td>
                        @if(app('company')['show_discount'])
                        <td class=" text-end">
                            {{ $formatNumber->formatWithPrecision($transaction->discount_amount) }}<br>
                            <small>
                                ({{ $formatNumber->formatWithPrecision($transaction->discount) }}
                                {{ ($transaction->discount_type == 'fixed') ? '$' : '%' }})
                            </small>
                        </td>
                        @endif
                        @if(app('company')['tax_type'] != 'no-tax')
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($transaction->tax_amount) }}<br>
                            <small>({{ $transaction->tax->rate ?? 0 }}%)</small>
                        </td>
                        @endif
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($transaction->total) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="fw-bold">
                        <td class="text-end" colspan="{{ 2 + $totalBatchTrackingRowCount + app('company')['show_mrp'] + app('company')['show_hsn'] }}">
                            {{ __('app.total') }}
                        </td>
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($sale->itemTransaction->sum('quantity')) }}
                        </td>
                        <td></td>
                        @if(app('company')['show_discount'])
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($sale->itemTransaction->sum('discount_amount')) }}
                        </td>
                        @endif
                        @if(app('company')['tax_type'] != 'no-tax')
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($sale->itemTransaction->sum('tax_amount')) }}
                        </td>
                        @endif
                        <td class="text-end">
                            {{ $formatNumber->formatWithPrecision($sale->itemTransaction->sum('total')) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    @php
                    $subtotal = $sale->itemTransaction->sum(function ($transaction) {
                    /*if($transaction->tax_type == 'inclusive'){
                    $unitPrice = calculatePrice($transaction->unit_price, $transaction->tax->rate, needInclusive: true);
                    }else{
                    $unitPrice = calculatePrice($transaction->unit_price, $transaction->tax->rate, needInclusive: false);
                    }*/
                    $unitPrice = $transaction->unit_price;
                    return $unitPrice * $transaction->quantity;
                    });
                    $discount = $sale->itemTransaction->sum(function ($transaction) {
                    return $transaction->discount_amount;
                    });

                    $taxAmount = $sale->itemTransaction->sum(function ($transaction) {
                    return $transaction->tax_amount;
                    });

                    @endphp

                    @php
                    $noteColumns = 4+ $totalBatchTrackingRowCount + app('company')['show_hsn'] + app('company')['show_mrp'] - ((app('company')['tax_type'] =='no-tax') ? 1 : 0) - (app('company')['show_discount'] ? 0 : 1);

                    $noteRosSpan = 4 - ((app('company')['tax_type'] =='no-tax') ? 1 : 0);

                    $amountInWordsRowSpan = 3 + app('company')['show_mrp'] + (app('company')['show_party_due_payment'] * 2) + app('company')['is_enable_secondary_currency'];
                    @endphp
                    <tr>
                        <td colspan="{{ $noteColumns }}" rowspan="{{$noteRosSpan}}" class="tfoot-first-td">
                            <span class="invoice-note">Note:<br></span>{{ $sale->note }}
                        </td>
                        <td colspan="2" class="text-end fw-bold">{{ __('app.subtotal') }}</td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($subtotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('app.discount') }}</td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($discount) }}</td>
                    </tr>
                    @if(app('company')['tax_type'] != 'no-tax')
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('tax.tax') }}</td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($taxAmount) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('app.round_off') }}</td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($sale->round_off) }}</td>
                    </tr>
                    <tr>
                        <td colspan="{{ $noteColumns }}" rowspan="{{ $amountInWordsRowSpan }}" class="tfoot-first-td">
                            <b>{{ __('app.amount_in_words') }}:<br></b>
                            {{ ucwords($formatNumber->spell($sale->grand_total)) }}
                        </td>
                        <td colspan="2" class="text-end fw-bold">{{ __('app.grand_total') }}</td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($sale->grand_total) }}</td>
                    </tr>
                    @if($sale->exchange_rate > 0)
                    <tr>
                        <td colspan="2" class="text-end fw-bold"><strong>{{ __('app.total_lebanon') }}</strong></td>
                        <td colspan="1" class="text-end">{{ $formatNumber->formatWithPrecision($sale->grand_total * $sale->exchange_rate) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('payment.paid_amount') }}</td>
                        <td colspan="1" class="text-end">{{$formatNumber->formatWithPrecision($sale->paid_amount)}}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('payment.balance') }}</td>
                        <td colspan="1" class="text-end fw-bold">{{$formatNumber->formatWithPrecision($sale->grand_total - $sale->paid_amount)}}</td>
                    </tr>
                    @if(app('company')['show_mrp'])
                    <tr>
                        @php
                        $savedAmount = $sale->itemTransaction->sum(function ($transaction) {
                        if($transaction->mrp > 0){
                        return ($transaction->mrp * $transaction->quantity) - $transaction->total;
                        }else{
                        return 0;
                        }
                        });

                        @endphp
                        <td colspan="2" class="text-end fw-bold">{{ __('app.you_saved') }}</td>
                        <td colspan="1" class="text-end">{{$formatNumber->formatWithPrecision($savedAmount)}}</td>
                    </tr>
                    @endif
                    @if(app('company')['show_party_due_payment'])
                    @php
                    $partyTotalDue = $sale->party->getPartyTotalDueBalance();
                    $partyTotalDueBalance = $partyTotalDue['balance'];
                    @endphp
                    <tr>

                        <td colspan="2" class="text-end fw-bold">{{ __('app.previous_due') }}</td>
                        <td colspan="1" class="text-end">{{$formatNumber->formatWithPrecision($partyTotalDueBalance - ($sale->grand_total - $sale->paid_amount))}}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">{{ __('app.total_due_balance'). ($partyTotalDue['status'] == 'you_pay' ? '(You Pay)' : '(Receive)')}}</td>
                        <td colspan="1" class="text-end">{{$formatNumber->formatWithPrecision($partyTotalDueBalance)}}</td>
                    </tr>
                    @endif

                </tfoot>
            </table>


            @if(app('company')['show_tax_summary'] && app('company')['tax_type'] != 'no-tax')
            <table class="table table-bordered custom-table tax-breakdown table-compact">
                <thead>
                    @if(app('company')['tax_type'] == 'tax')
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
                        <th colspan="2" class="text-center">{{ __('tax.cgst') }}</th>
                        <th colspan="2" class="text-center">{{ __('tax.sgst') }}</th>
                        <th colspan="2" class="text-center">{{ __('tax.igst') }}</th>
                        <th rowspan="2">{{ __('tax.tax_amount') }}</th>
                    </tr>
                    <tr>
                        <th>{{ __('tax.rate') }}%</th>
                        <th>{{ __('app.amount') }}</th>
                        <th>{{ __('tax.rate') }}%</th>
                        <th>{{ __('app.amount') }}</th>
                        <th>{{ __('tax.rate') }}%</th>
                        <th>{{ __('app.amount') }}</th>
                    </tr>
                    @endif
                </thead>
                <tbody>
                    @php

                    if(app('company')['tax_type'] == 'tax'){
                    $taxSummary = $sale->itemTransaction
                    ->groupBy('tax_id')
                    ->map(function ($group) {
                    $firstItem = $group->first();
                    $totalTaxableAmount = $group->sum(function ($item) use ($firstItem) {
                    $totalOfEachItem = ($item->unit_price-$item->discount_amount) * $item->quantity;
                    return $totalOfEachItem;
                    /*
                    if ($item->tax_type == 'inclusive') {
                    return calculatePrice($totalOfEachItem, $firstItem->tax->rate, needInclusive: true);
                    } else {
                    return calculatePrice($totalOfEachItem, $firstItem->tax->rate, needInclusive: false);
                    }*/
                    });
                    return [
    'tax_id' => $firstItem->tax_id,
    'tax_name' => $firstItem->tax->name ?? 'N/A',
    'tax_rate' => $firstItem->tax->rate ?? 0,
    'total_taxable_amount' => $totalTaxableAmount,
    'total_tax' => $group->sum('tax_amount')
];
                    })
                    ->values();
                    }
                    else{
                    //GST
                    $taxSummary = $sale->itemTransaction
                    ->groupBy('item.hsn') // First group by HSN
                    ->map(function ($hsnGroup) {
                    return $hsnGroup->groupBy('tax_id') // Then group by tax_id within each HSN group
                    ->map(function ($group) {
                    $firstItem = $group->first();
                    $totalTaxableAmount = $group->sum(function ($item) {
                    $totalOfEachItem = ($item->unit_price - $item->discount_amount) * $item->quantity;
                    return $totalOfEachItem;
                    /*
                    if ($item->tax_type == 'inclusive') {
                    return calculatePrice($totalOfEachItem, $item->tax->rate, needInclusive: true);
                    } else {
                    return calculatePrice($totalOfEachItem, $item->tax->rate, needInclusive: false);
                    }*/
                    });
                    return [
    'hsn' => $firstItem->item->hsn,
    'tax_id' => $firstItem->tax_id,
    'tax_name' => $firstItem->tax->name ?? 'N/A',
    'tax_rate' => $firstItem->tax->rate ?? 0,
    'total_taxable_amount' => $totalTaxableAmount,
    'total_tax' => $group->sum('tax_amount')
];
                    });
                    })
                    ->flatMap(function ($hsnGroup) {
                    return $hsnGroup;
                    })
                    ->values();
                    }

                    @endphp
                    @foreach($taxSummary as $summary)
                    @if(app('company')['tax_type'] == 'tax')
                    <tr>
                        <td>{{ $summary['tax_name'] }}</td>
                        <td class="text-end">{{ $formatNumber->formatWithPrecision($summary['total_taxable_amount']) }}</td>
                        <td class="text-center">{{ $summary['tax_rate'] }}%</td>
                        <td class="text-end">{{ $formatNumber->formatWithPrecision($summary['total_tax']) }}</td>
                    </tr>
                    @else
                    <tr>
                        <td>{{ $summary['hsn'] }}</td>
                        <td class="text-end">{{ $formatNumber->formatWithPrecision($summary['total_taxable_amount']) }}</td>

                        @php
                        $cs_gst = $i_gst = '';
                        $cs_gst_amt = $i_gst_amt = '';
                        if(empty($sale->state_id) || app('company')['state_id'] == $sale->state_id){
                        $cs_gst = ($summary['tax_rate']/2).'%';
                        $cs_gst_amt = $formatNumber->formatWithPrecision($summary['total_tax']/2);
                        }else{
                        $i_gst = ($summary['tax_rate']).'%';
                        $i_gst_amt = $formatNumber->formatWithPrecision($summary['total_tax']);
                        }
                        @endphp
                        <!-- CGST & SGT -->
                        <td class="text-center">{{ $cs_gst }}</td>
                        <td class="text-end">{{ $cs_gst_amt }}</td>
                        <td class="text-center">{{ $cs_gst }}</td>
                        <td class="text-end">{{ $cs_gst_amt }}</td>
                        <!-- IGST -->
                        <td class="text-center">{{ $i_gst }}</td>
                        <td class="text-end">{{ $i_gst_amt }}</td>
                        <td class="text-end">{{ $formatNumber->formatWithPrecision($summary['total_tax']) }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @endif

            @include('print.common.terms-conditions')

            @include('print.common.bank-signature')


        </div>
    </div>
</body>

</html>