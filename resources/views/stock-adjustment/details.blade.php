@extends('layouts.app')
@section('title', __('app.print'))

		@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<x-breadcrumb :langArray="[
											'item.stock',
                                            'warehouse.stock_adjustment',
                                            'app.print',
										]"/>
				<div class="row">
					<div class="col-12 col-lg-12">

                        @include('layouts.session')

                        <input type="hidden" id="base_url" value="{{ url('/') }}">

                        <div class="card">
                    <div class="card-body">
                        <div class="toolbar hidden-print">
                                <div class="text-end">

                                    @can(['stock_adjustment.edit'])
                                    <a href="{{ route('stock_adjustment.edit', ['id' => $adjustment->id]) }}" class="btn btn-outline-primary"><i class="bx bx-edit"></i>{{ __('app.edit') }}</a>
                                    @endcan

                                    <a href="{{ route('stock_adjustment.print', ['id' => $adjustment->id]) }}" target="_blank" class="btn btn-outline-secondary px-4"><i class="bx bx-printer mr-1"></i>{{ __("app.print") }}</a>

                                    <a href="{{ route('stock_adjustment.pdf', ['id' => $adjustment->id]) }}" target="_blank" class="btn btn-outline-danger px-4"><i class="bx bxs-file-pdf mr-1"></i>{{ __("app.pdf") }}</a>

                                </div>
                                <hr/>
                            </div>
                        <div id="printForm">
                            <div class="invoice overflow-auto">
                                <div class="min-width-600">
                                    <header>
                                        <div class="row">
                                            <div class="col">
                                                <a href="javascript:;">
                                                    <img src={{ "/company/getimage/" . app('company')['colored_logo'] }} width="80" alt="" />
                                                </a>
                                            </div>
                                            <div class="col company-details">
                                                <h2 class="name">
                                                    <a href="javascript:;">
                                                    {{ app('company')['name'] }}
                                                    </a>
                                                </h2>
                                                <div>{{ app('company')['address'] }}</div>
                                            </div>
                                        </div>
                                    </header>
                                    <main>
                                        <div class="row contacts">
                                            <div class="col invoice-to">
                                                <div class="text-gray-light fw-bold text-uppercase">{{ __('warehouse.stock_adjustment') }}</div>
                                            </div>

                                            <div class="col invoice-details">
                                                <h1 class="invoice-id">#{{ $adjustment->adjustment_code }}</h1>
                                                <div class="date">{{ __('app.date') }}: {{ $adjustment->formatted_adjustment_date  }}</div>
                                                @if($adjustment->reference_no)
                                                    <div class="date">{{ __('app.reference_no') }}: {{ $adjustment->reference_no  }}</div>
                                                @endif

                                            </div>
                                        </div>
                                        @php
                                            $isHasBatchItem = ($adjustment->itemTransaction->where('tracking_type', 'batch')->count() > 0) ? true : false;

                                            //Return from Controller
                                            $totalBatchTrackingRowCount = ($isHasBatchItem) ? $batchTrackingRowCount : 0;
                                        @endphp
                                        <table id="printInvoice">
                                            <thead>
                                                <tr class="text-uppercase">
                                                    <th>#</th>
                                                    <th class="text-left">{{ __('item.item') }}</th>
                                                    <th class="text-left {{ !app('company')['show_hsn'] ? 'd-none':'' }}">{{ __('item.hsn') }}</th>

                                                    @if($isHasBatchItem)
                                                        <th class="{{ !app('company')['enable_batch_tracking'] ? 'd-none':'' }}">{{ __('item.batch_no') }}</th>
                                                        <th class="{{ !app('company')['enable_mfg_date'] ? 'd-none':'' }}">{{ __('item.mfg_date') }}</th>
                                                        <th class="{{ !app('company')['enable_exp_date'] ? 'd-none':'' }}">{{ __('item.exp_date') }}</th>
                                                        <th class="{{ !app('company')['enable_model'] ? 'd-none':'' }}">{{ __('item.model_no') }}</th>
                                                        <th class="{{ !app('company')['enable_color'] ? 'd-none':'' }}">{{ __('item.color') }}</th>
                                                        <th class="{{ !app('company')['enable_size'] ? 'd-none':'' }}">{{ __('item.size') }}</th>
                                                    @endif
                                                    <th class="text-left">{{ __('app.qty') }}</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $i=1;
                                                @endphp

                                                @foreach($adjustment->itemTransaction as $transaction)
                                                <tr>
                                                    <td class="no">{{ $i++ }}</td>
                                                    <td class="text-left">
                                                        <h3>
                                                            <!-- Service Name -->
                                                            {{ $transaction->item->name }}
                                                        </h3>
                                                        <small>
                                                            <!-- Description -->
                                                            {{ $transaction->description }}
                                                        </small>
                                                        <small>
                                                            @if ($transaction->itemSerialTransaction->count() > 0)
                                                                <br>{{ $transaction->itemSerialTransaction->pluck('itemSerialMaster.serial_code')->implode(', ') }}<br>
                                                            @endif
                                                        </small>
                                                   </td>
                                                   <td class="{{ !app('company')['show_hsn'] ? 'd-none':'' }}">
                                                       {{ $transaction->item->hsn }}
                                                   </td>

                                                   @if($isHasBatchItem)
                                                        <td class="{{ !app('company')['enable_batch_tracking'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->batch_no : '' }}
                                                        </td>
                                                        <td class="{{ !app('company')['enable_mfg_date'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->mfg_date) : '' }}
                                                        </td>
                                                        <td class="{{ !app('company')['enable_exp_date'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $formatDate->toUserDateFormat($transaction->batch->itemBatchMaster->exp_date) : '' }}
                                                        </td>
                                                        <td class="{{ !app('company')['enable_model'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->model_no : ''}}
                                                        </td>
                                                        <td class="{{ !app('company')['enable_color'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->color :'' }}
                                                        </td>
                                                        <td class="{{ !app('company')['enable_size'] ? 'd-none':'' }}">
                                                            {{ $transaction->batch ? $transaction->batch->itemBatchMaster->size : '' }}
                                                        </td>
                                                   @endif
                                                   <td class="">
                                                        {{ $formatNumber->formatQuantity($transaction->quantity) }}
                                                    </td>

                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                @php
                                                $totalQuantity = $adjustment->itemTransaction->sum(function ($transaction) {
                                                            return $transaction->quantity;
                                                        });

                                                $columnCount = 2+ $totalBatchTrackingRowCount;
                                                @endphp
                                                <tr>
                                                    <td colspan="{{$columnCount}}" class="tfoot-first-td">{{ __('app.total') }}</td>
                                                    <td class="text-start">{{ $formatNumber->formatQuantity($totalQuantity) }}</td>
                                                </tr>

                                            </tfoot>
                                        </table>
                                    </main>

                                </div>
                                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                                <div></div>
                            </div>
                        </div>
                    </div>
                </div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>

		@endsection
