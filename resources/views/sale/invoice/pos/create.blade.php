@extends('layouts.app-pos')
@section('title', __('sale.pos'))

@section('css')
    <link rel="stylesheet" href="{{ versionedAsset('custom/css/pos.css') }}" />

    <link rel="stylesheet" href="{{ versionedAsset('custom/css/sidebar-pos.css') }}" />
@endsection
@section('content')
    <!--start page wrapper -->
    <nav class="navbar navbar-expand-lg navbar-light {{ $themeBgColor }} rounded fixed-top rounded-0 shadow-sm">
        <div class="container-fluid">

            <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Go to Dashboard">
                <h6 class="logo-text">{{ app('site')['name'] }}</h6>
            </a>

            @if(!empty($data['register_display']))
                <span class="ms-2 text-primary fw-semibold">
                    {{ $data['register_display'] }}
                </span>
            @endif

            <audio id="pos-sound" src="{{ asset('assets/sound/pos-sound.mp3') }}"></audio>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent1"
                aria-controls="navbarSupportedContent1" aria-expanded="false" aria-label="Toggle navigation"> <span
                    class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent1">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    @can('transaction.cash.close')
                        <li class="nav-item nav-item-close-cash">
                            <a class="nav-link" href="{{ route('transaction.close-cash') }}" target="_blank">
                                <i class='bx bx-wallet me-1'></i>{{ __('app.close_cash') }}
                            </a>
                        </li>
                    @endcan

                    <!-- Calculator Popup -->
                    <div id="calculator-popup" class="calculator-popup">
                        <div id="calculator-header" class="calculator-header">
                            <span>Calculator</span>
                            <span class="calculator-close" id="calculator-close">&times;</span>
                        </div>
                        <div id="calculator-body" class="calculator-body">
                            <input type="text" id="calc-display" class="calc-display" readonly>
                            <div class="calc-grid">
                                <button class="calc-btn" data-value="7">7</button>
                                <button class="calc-btn" data-value="8">8</button>
                                <button class="calc-btn" data-value="9">9</button>
                                <button class="calc-btn" data-value="/">&#247;</button>
                                <button class="calc-btn" data-value="4">4</button>
                                <button class="calc-btn" data-value="5">5</button>
                                <button class="calc-btn" data-value="6">6</button>
                                <button class="calc-btn" data-value="*">&times;</button>
                                <button class="calc-btn" data-value="1">1</button>
                                <button class="calc-btn" data-value="2">2</button>
                                <button class="calc-btn" data-value="3">3</button>
                                <button class="calc-btn" data-value="-">-</button>
                                <button class="calc-btn" data-value="0">0</button>
                                <button class="calc-btn" data-value=".">.</button>
                                <button class="calc-btn" id="calc-equals">=</button>
                                <button class="calc-btn" data-value="+">+</button>
                                <button class="calc-btn" id="calc-clear">C</button>
                                <button class="calc-btn" id="calc-backspace">&#8592;</button>
                            </div>
                        </div>
                    </div>
                    <!-- End Calculator Popup -->

                    <li class="nav-item"> <a class="nav-link" aria-current="page" href="{{ route('dashboard') }}"><i
                                class='bx bx-home-alt me-1'></i>{{ __('app.dashboard') }}</a>
                    </li>
                    @can('customer.view')
                        <li class="nav-item"> <a class="nav-link"
                                href="{{ route('party.list', ['partyType' => 'customer']) }}"><i
                                    class='bx bx-group me-1'></i>{{ __('customer.list') }}</a>
                        </li>
                    @endcan
                    @can('sale.invoice.view')
                        <li class="nav-item"> <a class="nav-link" href="{{ route('sale.invoice.list') }}"><i
                                    class='bx bx-cart me-1'></i>{{ __('sale.invoices') }}</a>
                        </li>
                    @endcan
                    @can('item.view')
                        <li class="nav-item"> <a class="nav-link" href="{{ route('item.list') }}"><i
                                    class='bx bx-package me-1'></i>{{ __('item.list') }}</a>
                        </li>
                    @endcan
                    @can('sale.invoice.view')
                        <li class="nav-item"> <a class="nav-link" href="{{ route('sale.payment.in') }}"><i
                                    class='bx bx-money me-1'></i>{{ __('payment.payment_in') }}</a>
                        </li>
                    @endcan
                </ul>
            </div>
        </div>
    </nav>
    <form class="" id="invoiceForm" action="{{ route('sale.invoice.store') }}" enctype="multipart/form-data">
        {{-- CSRF Protection --}}
        @csrf
        @method('POST')
        <input type="hidden" name="row_count" value="0">
        <input type="hidden" name="row_count_payments" value="2">
        <input type="hidden" id="base_url" value="{{ url('/') }}">
        <input type="hidden" id="operation" name="operation" value="save">
        <input type="hidden" name="is_pos_form" value="true">
        <input type="hidden" id="selectedPaymentTypesArray" value="{{ $selectedPaymentTypesArray }}">
        <input type="hidden" id="invoice_status" name="invoice_status" value="finished">

        <div class="page-wrapper-1">
            <div class="container-fluid mt-5">
                <div class="row">
                    <div class="col-sm-12 col-md-5 mb-3">
                        <div class="mb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <!-- left -->
                                <div class="flex-shrink-0">
                                    <x-dropdown-sorting-pos />
                                    <input type="hidden" id="sorting_preference"
                                        value="{{ auth()->user()->pos_sorting_preference ?? 'a_to_z' }}">
                                </div>

                                <!-- center -->
                                <div class="flex-shrink-0">
                                    <div class="input-group">
                                        <x-input type="text" additionalClasses="datepicker no-pointer"
                                            name="sale_date_display" :required="false" value="{{ date('Y-m-d') }}"
                                            readonly />
                                        <input type="hidden" name="sale_date" value="{{ date('Y-m-d') }}">
                                        <span class="input-group-text" id="input-near-focus" role="button">
                                            <i class="fadeIn animated bx bx-calendar-alt"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- right -->
                                <div class="flex-shrink-0 d-flex align-items-center gap-2 pos-actions-container">
                                    <!-- Calculator Icon -->
                                    <div class="calculator-icon-container">
                                        <a class="nav-link" href="javascript:void(0);" id="calculator-toggle"
                                            title="Calculator">
                                            <i class='bx bx-calculator'></i>
                                        </a>
                                        <!-- Calculator Popup -->
                                        <div id="calculator-popup" class="calculator-popup">
                                            <div id="calculator-header" class="calculator-header">
                                                <span>Calculator</span>
                                                <span class="calculator-close" id="calculator-close">&times;</span>
                                            </div>
                                            <div id="calculator-body" class="calculator-body">
                                                <input type="text" id="calc-display" class="calc-display" readonly>
                                                <div class="calc-grid">
                                                    <button class="calc-btn" data-value="7">7</button>
                                                    <button class="calc-btn" data-value="8">8</button>
                                                    <button class="calc-btn" data-value="9">9</button>
                                                    <button class="calc-btn" data-value="/">&#247;</button>
                                                    <button class="calc-btn" data-value="4">4</button>
                                                    <button class="calc-btn" data-value="5">5</button>
                                                    <button class="calc-btn" data-value="6">6</button>
                                                    <button class="calc-btn" data-value="*">&times;</button>
                                                    <button class="calc-btn" data-value="1">1</button>
                                                    <button class="calc-btn" data-value="2">2</button>
                                                    <button class="calc-btn" data-value="3">3</button>
                                                    <button class="calc-btn" data-value="-">-</button>
                                                    <button class="calc-btn" data-value="0">0</button>
                                                    <button class="calc-btn" data-value=".">.</button>
                                                    <button class="calc-btn" id="calc-equals">=</button>
                                                    <button class="calc-btn" data-value="+">+</button>
                                                    <button class="calc-btn" id="calc-clear">C</button>
                                                    <button class="calc-btn" id="calc-backspace">&#8592;</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!empty($data['pending_invoices_count']) && $data['pending_invoices_count'] > 0)
                                        <span class="pos-pending-badge">
                                            {{ $data['pending_invoices_count'] }}
                                        </span>
                                    @endif
                                    <button type="button"
                                        class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center hand-btn">
                                        <i class='bx bxs-hand bx-tada'></i>
                                    </button>
                                    <x-dropdown-warehouse selected="" dropdownName="warehouse_id" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <x-dropdown-item-category selected="" :isMultiple="false" :showSelectOptionAll="true" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-dropdown-brand selected="" :showSelectOptionAll='true' name="item_brand_id" />
                                </div>
                            </div>
                        </div>
                        <div id="itemsGridContainer">
                            <div class="row row-cols-2 row-cols-md-4 row-cols-sm-4 row-cols-xs-4 g-4 p-2" id="itemsGrid">
                            </div>
                            <div class="text-center my-4">
                                <button id="loadMoreBtn" class="btn btn-sm btn-outline-primary px-5 rounded-1" type="button"
                                    tabindex="0">Load More</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-7 mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <x-input type="text" name="prefix_code" :required="true" placeholder="Prefix Code"
                                        value="{{ $data['prefix_code'] }}" readonly />
                                    <span class="input-group-text">#</span>
                                    <x-input type="text" name="count_id" :required="true" placeholder="Serial Number"
                                        value="{{ $data['count_id'] }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-select party-ajax" data-party-type='customer'
                                        data-placeholder="Select Customer" id="party_id" name="party_id">
                                        <x-option-default-party-selected partyType='customer' />
                                    </select>
                                    <button type="button" class="input-group-text open-party-model"
                                        data-party-type='customer'>
                                        <i class='text-primary bx bx-plus-circle'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="fadeIn animated bx bx-barcode-reader text-primary"></i></span>
                                    <input type="text" id="search_item" value="" class="form-control" required
                                        placeholder="Scan Barcode/Search Items">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#itemModal"><i class="bx bx-plus-circle me-0"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        {{-- Add checkbox above the table --}}
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="serial_number_checkbox">
                                                    <label class="form-check-label"
                                                        for="serial_number_checkbox">{{ __('item.serial_number') }}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="resizableDiv" class="table-responsive resizable-vertical">
                                            <table class="table mb-0 table-striped table-bordered" id="invoiceItemsTable">
                                                <thead>
                                                    <tr class="text-uppercase">
                                                        <th scope="col">{{ __('item.item') }}</th>
                                                        <th scope="col" class="d-none serial-column">
                                                            {{ __('item.serial') }}
                                                        </th>
                                                        <!---<th scope="col" class="{{ !app('company')['enable_batch_tracking'] ? 'd-none' : '' }}">{{ __('item.batch_no') }}</th>
                                                                                                                            <th scope="col" class="{{ !app('company')['enable_mfg_date'] ? 'd-none' : '' }}">{{ __('item.mfg_date') }}</th>
                                                                                                                            <th scope="col" class="{{ !app('company')['enable_exp_date'] ? 'd-none' : '' }}">{{ __('item.exp_date') }}</th>
                                                                                                                            <th scope="col" class="{{ !app('company')['enable_model'] ? 'd-none' : '' }}">{{ __('item.model_no') }}</th>
                                                                                                                            <th scope="col" class="{{ !app('company')['show_mrp'] ? 'd-none' : '' }}">{{ __('item.mrp') }}</th>--->
                                                        <th scope="col"
                                                            class="{{ !app('company')['enable_color'] ? 'd-none' : '' }}">
                                                            {{ __('item.color') }}</th>
                                                        <th scope="col"
                                                            class="{{ !app('company')['enable_size'] ? 'd-none' : '' }}">
                                                            {{ __('item.size') }}</th>
                                                        <th scope="col" class="col-md-1">{{ __('app.qty') }}</th>
                                                        <th scope="col">{{ __('unit.unit') }}</th>
                                                        <th scope="col">{{ __('app.price_per_unit') }}</th>
                                                        <th scope="col"
                                                            class="{{ !app('company')['show_discount'] ? 'd-none' : '' }}">
                                                            {{ __('app.discount') }}</th>
                                                        <th scope="col"
                                                            class="{{ app('company')['tax_type'] == 'no-tax' ? 'd-none' : '' }}">
                                                            {{ __('tax.tax') }} %</th>
                                                        <th scope="col">{{ __('app.total') }}</th>
                                                        <th scope="col">{{ __('app.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="9" class="text-center fw-light fst-italic default-row">
                                                            No items are added yet !!
                                                        </td>
                                                    </tr>
                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8 d-none">
                                    <x-label for="note" name="{{ __('app.note') }}" />
                                    <x-textarea name='note' value='' />
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-3 col-sm-6"><strong>Total Quantity:</strong> <span id="totalQuantity"
                                            class="sum_of_quantity">0</span></div>
                                    <div class="col-md-3 col-sm-6"><strong>Discount:</strong> <span id="totalDiscount"
                                            class="sum_of_discount">0.00</span></div>
                                    <div class="col-md-3 col-sm-6"><strong>Tax:</strong> <span id="totalTax"
                                            class="sum_of_tax">0.00</span></div>
                                    <div class="col-md-3 col-sm-6"><strong>Total Price:</strong> <span id="totalPrice"
                                            class="sum_of_total">0.00</span></div>
                                </div>

                            </div>

                            <div class="col-md-6 mt-4">
                                <table class="table mb-0 table-striped">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-select select2 payment-type-ajax"
                                                    name="payment_type_id[0]" data-placeholder="Choose one thing">
                                                </select>
                                            </td>
                                            <td class="w-50">
                                                <x-input type="text" additionalClasses="text-end cu_numeric"
                                                    name="payment_amount[0]" :required="false" placeholder="Payment Amount"
                                                    value="0" />
                                                <input type="hidden" name="payment_note[0]" value="">
                                            </td>
                                        </tr>

                                        <tr class="d-none">
                                            <td>
                                                <select class="form-select select2 payment-type-ajax"
                                                    name="payment_type_id[1]" data-placeholder="Choose one thing">
                                                </select>
                                            </td>
                                            <td class="w-50">
                                                <x-input type="text" additionalClasses="text-end cu_numeric"
                                                    name="payment_amount[1]" :required="false" placeholder="Payment Amount"
                                                    value="0" />
                                                <input type="hidden" name="payment_note[1]" value="">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-end">
                                                <label class="fw-bold" for="round_off_checkbox">{{ __('payment.balance')
                                                    }}</label>
                                            </td>
                                            <td class="w-50 text-end">
                                                <label class="fw-bold balance" for="round_off_checkbox">0</label>
                                            </td>
                                        </tr>
                                        <tr class="change_return_parent">
                                            <td class="text-end">
                                                <label class="fw-bold align-middle " for="change_return">{{
                                                    __('payment.change_return') }}</label>
                                            </td>
                                            <td class="w-50 text-end">
                                                <label class="fw-bold change_return text-danger fs-2"
                                                    for="change_return">0</label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="add-payment-type-parent">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class="bx bx-plus text-primary font-18 me-1"></i></div>
                                        <a href="javascript:void(0);"
                                            class="tab-title text-decoration-none add-payment-type">Add Payment Type</a>
                                    </div>
                                </div>


                            </div>

                            <div class="col-md-6 mt-4">
                                <table class="table mb-0 table-striped">
                                    <tbody>
                                        <tr>
                                            <td class="w-50">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="round_off_checkbox">
                                                    <label class="form-check-label fw-bold cursor-pointer"
                                                        for="round_off_checkbox">{{ __('app.round_off') }}</label>
                                                </div>
                                            </td>
                                            <td class="w-50">
                                                <x-input type="text" additionalClasses="text-end cu_numeric round_off "
                                                    name="round_off" :required="false" placeholder="Round-Off" value="0" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><span class="fw-bold">{{ __('app.grand_total') }}</span></td>
                                            <td>
                                                <x-input type="text" additionalClasses="text-end grand_total" readonly=true
                                                    name="grand_total" :required="true" placeholder="Round-Off" value="0" />
                                            </td>
                                        </tr>
                                        @if (app('company')['is_enable_secondary_currency'])
                                        <tr>
                                            <td>
                                                <div class="input-group mb-3">
                                                    <x-dropdown-currency selected="" name='invoice_currency_id' />
                                                    <x-input type="text" name="exchange_rate" :required="false"
                                                        additionalClasses='cu_numeric' value="0" />
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <x-input type="text" additionalClasses="text-end converted_amount"
                                                    readonly=true :required="true" placeholder="Converted Amount"
                                                    value="0" />
                                                <span class="fw-bold exchange-lang text-end"
                                                    data-exchange-lang="{{ __('currency.converted_to') }}">{{
                                                    __('currency.exchange') }}</span>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>

                </div>
            </div>
        </div>
        <div class="{{ $themeBgColor }} p-2 fixed-bottom border-top shadow">
            <div class="container-fluid d-flex justify-content-end gap-3">
                <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                <x-anchor-tag href="{{ route('pos.create') }}" text="{{ __('app.new') }}" class="btn btn-secondary px-4" />
                <button type="button" class="btn btn-warning" id="hold_button">Hold</button>
                <button type="button" class="btn btn-success" id="submit_form_with_print">{{ __('app.save_and_print')
                    }}</button>
                <!---<button type="button" class="btn btn-primary" id="submit_form_with_print">{{ __('app.save_and_print') }}</button>--->
                <!---<button type="button" class="btn btn-success" id="submit_form">{{ __('app.save') }}</button>--->
            </div>
        </div>
    </form>

    <!-- Import Modals -->
    @include('modals.service.create')
    @include('modals.expense-category.create')

    @include('modals.item.serial-tracking')
    @include('modals.item.batch-tracking-sale')
    @include('modals.party.create')
    @include('modals.item.create')

    @include('sale.invoice.pos.sidebar-pos')

@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/sale/sortable.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/sale/pos.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/currency-exchange.js') }}"></script>

    @php
        use App\Http\Controllers\CustomizationController;
        $toggleSwitch = DB::table('customizations')->where('key', 'toggle_switch')->value('value');
        $trendingIds = app(CustomizationController::class)->trendingItems();
    @endphp

    <script>
        window.toggleSwitchActive = '{{ $toggleSwitch }}' === 'active';
        window.trendingIds = @json($trendingIds);
        window.customerDisplayRoute = "{{ route('sale.invoice.customer-display') }}";
        window.allowNegativeStockBilling = {{ app('company')['allow_negative_stock_billing'] ? 'true' : 'false' }};
    </script>

    <script src="{{ versionedAsset('custom/js/sale/pos-item-scroller.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/serial-tracking-settings.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/batch-tracking-sale.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/payment-types/payment-type-select2-ajax.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/party/party.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/item/item.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/dropdown-sorting-pos.js') }}"></script>
    <script src="{{ asset('custom/js/sale/sidebar-pos.js') }}"></script>
@endsection