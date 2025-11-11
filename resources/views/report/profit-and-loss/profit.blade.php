@extends('layouts.app')
@section('title', __('account.profit_and_loss'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.reports', 'account.profit_and_loss']" />
            <div class="row">

                <div class="col">

                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-success" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profit_summary_tab"
                                        role="tab" aria-selected="true">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-dollar font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_summary') }}</div>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profit_by_invoice_tab" role="tab"
                                        aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-file font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_by_invoice') }}</div>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profit_by_items_tab" role="tab"
                                        aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-package font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_by_items') }}</div>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profit_by_brands_tab" role="tab"
                                        aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-copy font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_by_brands') }}</div>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profit_by_categories_tab" role="tab"
                                        aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-cabinet font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_by_categories') }}</div>
                                        </div>
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#profit_by_customers_tab" role="tab"
                                        aria-selected="false">
                                        <div class="d-flex align-items-center">
                                            <div class="tab-icon"><i class='bx bx-group font-18 me-1'></i>
                                            </div>
                                            <div class="tab-title">{{ __('account.profit_by_customers') }}</div>
                                        </div>
                                    </a>
                                </li>

                            </ul>
                            <div class="tab-content py-3">
                                <div class="tab-pane fade show active" id="profit_summary_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="reportForm"
                                        action="{{ route('report.profit_and_loss.ajax') }}" enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')

                                        <input type="hidden" name="row_count" value="0">
                                        <input type="hidden" name="total_amount" value="0">
                                        <input type="hidden" id="base_url" value="{{ url('/') }}">
                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date" name="{{ __('app.from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date" name="{{ __('app.to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="warehouse_id"
                                                            name="{{ __('warehouse.warehouse') }}" />
                                                        <select class="warehouse-ajax form-select"
                                                            data-placeholder="Select Warehouse" id="warehouse_id"
                                                            name="warehouse_id"></select>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_1"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_1">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.summary_and_gross_profit') }}</h5>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6 table-responsive">
                                                        <div class="text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="reportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="reportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                        </div>
                                                        <br>
                                                        <table id="reportTable" class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('item.transaction_type') }}</th>
                                                                    <th>{{ __('app.total_amount') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>{{ __('sale.sale_without_tax') }} (+)</td>
                                                                    <td id="sale_without_tax" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ __('sale.sale_return_without_tax') }} (-)</td>
                                                                    <td id="sale_return_without_tax" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ __('purchase.purchase_without_tax') }} (-)</td>
                                                                    <td id="purchase_without_tax" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ __('purchase.purchase_return_without_tax') }}
                                                                        (+)</td>
                                                                    <td id="purchase_return_without_tax" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr class="fw-bold">
                                                                    <td>{{ __('account.gross_profit') }}</td>
                                                                    <td id="gross_profit" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ __('account.expense_without_tax') }} (-)</td>
                                                                    <td id="indirect_expense_without_tax" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ __('carrier.shipping_charge') }} (-)</td>
                                                                    <td id="shipping_charge" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr class="fw-bold">
                                                                    <td>{{ __('account.net_summary') }}</td>
                                                                    <td id="net_profit" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6 table-responsive">
                                                        <div class="text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="profitReportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="profitReportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                        </div>
                                                        <br>
                                                        <table id="profitReportTable"
                                                            class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('item.transaction_type') }}</th>
                                                                    <th>{{ __('app.total_amount') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        {{ __('account.gross_profit') }}<br>
                                                                        <small>
                                                                            <i class="text-secondary">{{ __('account.gross_profit') }}
                                                                                = {{ __('sale.sale_amount') }} -
                                                                                {{ __('purchase.purchase_cost') }}</i>
                                                                            <br>
                                                                            <i class="text-secondary">
                                                                                {{ __('app.note') . ' : ' . __('purchase.we_calcuating_avg_purchase_price_of_item') }}

                                                                            </i>
                                                                        </small>
                                                                    </td>
                                                                    <td id="sale_gross_profit" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        {{ __('account.net_profit') }}<br>
                                                                        <small>

                                                                            <i class="text-secondary">{{ __('account.net_profit') }}
                                                                                =
                                                                                {{ __('account.gross_profit') . ' - ' . __('tax.tax_amount') . ' (' . __('sale.sale') . ')' }}</i>
                                                                        </small>
                                                                    </td>
                                                                    <td id="sale_net_profit" class='text-end'
                                                                        data-tableexport-celltype="number">0.00</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="profit_by_invoice_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="profitByInvoiceReportForm"
                                        action="{{ route('report.invoice_wise_profit_and_loss.ajax') }}"
                                        enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')


                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date"
                                                            name="{{ __('app.invoice_from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date" name="{{ __('app.invoice_to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="sale_id" name="{{ __('sale.invoice_no') }}" />
                                                        <select class="invoice-ajax form-select"
                                                            data-placeholder="Select Invoice" id="sale_id"
                                                            name="sale_id"></select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_2"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_2">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.itew_wise_profit_and_loss') }}</h5>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="invoiceWiseReportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="invoiceWiseReportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12 table-responsive">
                                                        <table class="table table-bordered" id="invoiceWiseReportTable">
                                                            <thead>
                                                                <tr class="text-uppercase">
                                                                    <th>#</th>
                                                                    <th>{{ __('app.date') }}</th>
                                                                    <th>{{ __('sale.code') }}</th>
                                                                    <th>{{ __('customer.customer') }}</th>
                                                                    <th>{{ __('sale.sale_amount') }}</th>
                                                                    <th>{{ __('purchase.purchase_cost') }}</th>
                                                                    <th>{{ __('tax.tax_amount') }}</th>
                                                                    <th>{{ __('account.gross_profit') }}</th>
                                                                    {{-- Before Tax --}}
                                                                    <th>{{ __('account.net_profit') }}</th>
                                                                    {{-- After Tax --}}
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="profit_by_items_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="profitByItemReportForm"
                                        action="{{ route('report.item_wise_profit_and_loss.ajax') }}"
                                        enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')


                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date"
                                                            name="{{ __('app.invoice_from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date" name="{{ __('app.invoice_to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="item_id" name="{{ __('item.item_name') }}" />
                                                        <select class="item-ajax form-select"
                                                            data-placeholder="Select Item" id="item_id"
                                                            name="item_id"></select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="brand_id" name="{{ __('item.brand.brand') }}" />
                                                        <select class="brand-ajax form-select"
                                                            data-placeholder="Select Brand" id="brand_id"
                                                            name="brand_id"></select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="item_warehouse_id"
                                                            name="{{ __('warehouse.warehouse') }}" />
                                                        <select class="warehouse-ajax form-select"
                                                            data-placeholder="Select Warehouse" id="item_warehouse_id"
                                                            name="item_warehouse_id"></select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_3"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_3">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.itew_wise_profit_and_loss') }}</h5>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="itemWiseReportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="itemWiseReportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12 table-responsive">
                                                        <table class="table table-bordered" id="itemWiseReportTable">
                                                            <thead>
                                                                <tr class="text-uppercase">
                                                                    <th>#</th>
                                                                    <th>{{ __('item.item_name') }}</th>
                                                                    <th>{{ __('item.brand.brand') }}</th>
                                                                    <th>{{ __('item.avg_sale_price') }}</th>
                                                                    <th>{{ __('item.quantity') }}</th>
                                                                    <th>{{ __('item.avg_purchase_price') }}</th>
                                                                    <th>{{ __('sale.sale_total') }}</th>
                                                                    <th>{{ __('purchase.purchase_total') }}</th>
                                                                    <th>{{ __('account.gross_profit') }}</th>
                                                                    <th>{{ __('account.net_profit') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="profit_by_brands_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="profitByBrandReportForm"
                                        action="{{ route('report.brand_wise_profit_and_loss.ajax') }}"
                                        enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')


                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date"
                                                            name="{{ __('app.invoice_from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date" name="{{ __('app.invoice_to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="brand_id" name="{{ __('item.brand.brand') }}" />
                                                        <select class="brand-ajax form-select"
                                                            data-placeholder="Select Brand" id="item_brand_id"
                                                            name="brand_id"></select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="brand_warehouse_id"
                                                            name="{{ __('warehouse.warehouse') }}" />
                                                        <select class="warehouse-ajax form-select"
                                                            data-placeholder="Select Warehouse" id="brand_warehouse_id"
                                                            name="brand_warehouse_id"></select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_4"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_4">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.brand_wise_profit_and_loss') }}</h5>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="brandWiseReportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="brandWiseReportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12 table-responsive">
                                                        <table class="table table-bordered" id="brandWiseReportTable">
                                                            <thead>
                                                                <tr class="text-uppercase">
                                                                    <th>#</th>
                                                                    <th>{{ __('item.brand.brand') }}</th>
                                                                    <th>{{ __('item.quantity') }}</th>
                                                                    <th>{{ __('sale.sale_amount') }}</th>
                                                                    <th>{{ __('purchase.purchase_cost') }}</th>
                                                                    <th>{{ __('tax.tax_amount') }}</th>
                                                                    <th>{{ __('account.gross_profit') }}</th>
                                                                    {{-- Before Tax --}}
                                                                    <th>{{ __('account.net_profit') }}</th>
                                                                    {{-- After Tax --}}
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="profit_by_categories_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="profitByCategoryReportForm"
                                        action="{{ route('report.category_wise_profit_and_loss.ajax') }}"
                                        enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')


                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date"
                                                            name="{{ __('app.invoice_from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date"
                                                            name="{{ __('app.invoice_to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="category_id"
                                                            name="{{ __('item.category.category') }}" />
                                                        <select class="item-category-ajax form-select"
                                                            data-placeholder="Select Category" id="category_id"
                                                            name="category_id"></select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="category_warehouse_id"
                                                            name="{{ __('warehouse.warehouse') }}" />
                                                        <select class="warehouse-ajax form-select"
                                                            data-placeholder="Select Warehouse" id="category_warehouse_id"
                                                            name="category_warehouse_id"></select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_5"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_5">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.category_wise_profit_and_loss') }}</h5>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12 table-responsive">
                                                        <table class="table table-bordered" id="categoryWiseReportTable">
                                                            <thead>
                                                                <tr class="text-uppercase">
                                                                    <th>#</th>
                                                                    <th>{{ __('item.category.category') }}</th>
                                                                    <th>{{ __('item.quantity') }}</th>
                                                                    <th>{{ __('sale.sale_amount') }}</th>
                                                                    <th>{{ __('purchase.purchase_cost') }}</th>
                                                                    <th>{{ __('tax.tax_amount') }}</th>
                                                                    <th>{{ __('account.gross_profit') }}</th>
                                                                    {{-- Before Tax --}}
                                                                    <th>{{ __('account.net_profit') }}</th>
                                                                    {{-- After Tax --}}
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="profit_by_customers_tab" role="tabpanel">
                                    <form class="row g-3 needs-validation" id="profitByCustomerReportForm"
                                        action="{{ route('report.customer_wise_profit_and_loss.ajax') }}"
                                        enctype="multipart/form-data">
                                        {{-- CSRF Protection --}}
                                        @csrf
                                        @method('POST')


                                        <div class="col-12 col-lg-12">

                                            <div class="">
                                                <div class="card-body p-4 row g-3">

                                                    <div class="col-md-6">
                                                        <x-label for="from_date"
                                                            name="{{ __('app.invoice_from_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text"
                                                                additionalClasses="datepicker-month-first-date"
                                                                name="from_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-label for="to_date"
                                                            name="{{ __('app.invoice_to_date') }}" />
                                                        <div class="input-group mb-3">
                                                            <x-input type="text" additionalClasses="datepicker"
                                                                name="to_date" :required="true" value="" />
                                                            <span class="input-group-text" id="input-near-focus"
                                                                role="button"><i
                                                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <x-label for="party_id" name="{{ __('customer.customer') }}" />
                                                        <select class="form-select party-ajax" data-party-type='customer'
                                                            data-placeholder="Select Customer" id="party_id"
                                                            name="party_id"></select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" id="use_sale_avg_date_range_6"
                                                                name="use_sale_avg_date_range">
                                                            <label class="form-check-label"
                                                                for="use_sale_avg_date_range_6">
                                                                {{ __('account.use_only_selected_date_range') }}
                                                            </label>
                                                            <a tabindex="0" class="text-primary"
                                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                                data-bs-content="{{ __('account.use_only_selected_date_range_tooltip') }}"><i
                                                                    class="fadeIn animated bx bx-info-circle"></i></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12">
                                                        <div
                                                            class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                                            <x-button type="submit" class="primary px-4"
                                                                text="{{ __('app.submit') }}" />
                                                            <x-anchor-tag href="{{ route('dashboard') }}"
                                                                text="{{ __('app.close') }}"
                                                                class="btn btn-light px-4" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header px-4 py-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h5 class="mb-0">
                                                                {{ __('account.customer_wise_profit_and_loss') }}</h5>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <div class="btn-group">
                                                                <button type="button"
                                                                    class="btn btn-outline-success">{{ __('app.export') }}</button>
                                                                <button type="button"
                                                                    class="btn btn-outline-success dropdown-toggle dropdown-toggle-split"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"> <span
                                                                        class="visually-hidden">Toggle Dropdown</span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_excel"
                                                                            data-table-id="customerWiseReportTable"><i
                                                                                class="bx bx-spreadsheet mr-1"></i>{{ __('app.excel') }}</button>
                                                                    </li>
                                                                    <li><button type='button'
                                                                            class="dropdown-item generate_pdf"
                                                                            data-table-id="customerWiseReportTable"><i
                                                                                class="bx bx-file mr-1"></i>{{ __('app.pdf') }}</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="card-body p-4 row g-3">
                                                    <div class="col-md-12 table-responsive">
                                                        <table class="table table-bordered" id="customerWiseReportTable">
                                                            <thead>
                                                                <tr class="text-uppercase">
                                                                    <th>#</th>
                                                                    <th>{{ __('customer.customer') }}</th>
                                                                    <th>{{ __('sale.sale_amount') }}</th>
                                                                    <th>{{ __('purchase.purchase_cost') }}</th>
                                                                    <th>{{ __('tax.tax_amount') }}</th>
                                                                    <th>{{ __('account.gross_profit') }}</th>
                                                                    {{-- Before Tax --}}
                                                                    <th>{{ __('account.net_profit') }}</th>
                                                                    {{-- After Tax --}}
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!--end row-->
        </div>
    </div>
    <!-- Import Modals -->

@endsection

@section('js')
    @include('plugin.export-table')
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/reports/profit-and-loss/profit.js') }}"></script>
@endsection
