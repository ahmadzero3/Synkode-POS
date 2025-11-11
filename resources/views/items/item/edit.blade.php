@extends('layouts.app')
@section('title', __('item.update'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.items', 'item.list', 'item.update']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">


                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('item.details') }} <span
                                    class="text-primary">({{ $item->is_service ? __('service.service') : __('item.product') }})</span>
                            </h5>
                            <div class="btn-group d-none">
                                <input type="radio" class="btn-check" name="item_type_radio" id="product" value="product"
                                    autocomplete="off" {{ !$item->is_service ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm"
                                    for="product">{{ __('item.product') }}</label>
                                <input type="radio" class="btn-check" name="item_type_radio" id="service"
                                    value="service" autocomplete="off" {{ $item->is_service ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary btn-sm"
                                    for="service">{{ __('service.service') }}</label>
                            </div>
                        </div>


                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="itemForm" action="{{ route('item.update') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')

                                {{-- Units Modal --}}
                                @include('modals.unit.create')

                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <input type="hidden" id="operation" name="operation" value="update">
                                <input type="hidden" id="base_url" value="{{ url('/') }}">
                                <input type="hidden" name="serial_number_json" value='{{ $serviceJson }}'>
                                <input type="hidden" name="batch_details_json" value='{{ $batchJson }}'>
                                <input type="hidden" name="is_service" value='{{ $item->is_service ? 1 : 0 }}'>

                                <div class="col-md-4">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $item->name }}" />
                                </div>
                                @if (app('company')['show_hsn'])
                                    <div class="col-md-4">
                                        <x-label for="hsn" name="{{ __('item.hsn') }}" />
                                        <x-input type="text" name="hsn" :required="false"
                                            value="{{ $item->hsn }}" />
                                    </div>
                                @endif
                                @if (app('company')['show_sku'])
                                    <div class="col-md-4">
                                        <x-label for="sku" name="{{ __('item.sku') }}" />
                                        <x-input type="text" name="sku" :required="false"
                                            value="{{ $item->sku }}" />
                                    </div>
                                @endif

                                <div class="col-md-4">
                                    <x-label for="brand_id" name="{{ __('item.brand.brand') }}" />
                                    <div class="input-group">
                                        <x-dropdown-brand selected="{{ $item->brand_id }}" :showSelectOptionAll=true />
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#brandModal"><i class="bx bx-plus-circle me-0"></i>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="hsn" name="{{ __('item.code') }}" />
                                    <div class="input-group mb-3">
                                        <x-input type="text" name="item_code" :required="true"
                                            value="{{ $item->item_code }}" />
                                        <button class="btn btn-outline-secondary auto-generate-code" type="button"
                                            id="button-addon2">{{ __('app.auto') }}</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <x-label for="item_category_id" name="{{ __('item.category.category') }}" />
                                    <div class="input-group">
                                        <x-dropdown-item-category selected="{{ $item->item_category_id }}"
                                            :isMultiple=false />
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#itemCategoryModal"><i class="bx bx-plus-circle me-0"></i>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="description" name="{{ __('app.description') }}" />
                                    <x-textarea name="description" value="{{ $item->description }}" />
                                </div>

                                <div class="col-md-4 p-4 item-type-product">
                                    <button type="button" class="btn btn-light px-5 rounded-0" data-bs-toggle="modal"
                                        data-bs-target="#unitModal">{{ __('unit.select_unit') }}</button>
                                    <label class="primary unit-label"></label>
                                </div>



                                <div class="col-md-4 d-none">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $item->status }}" dropdownName='status' />
                                </div>

                                <div class="col-md-12 mt-5 item-type-product">
                                    <div class="d-flex align-items-center gap-3">

                                        <x-radio-block id="regular_tracking" boxName="tracking_type"
                                            text="{{ __('item.regular') }}" value="regular" boxType="radio"
                                            parentDivClass="fw-bold" :checked=true />

                                        @if (app('company')['enable_batch_tracking'])
                                            <x-radio-block id="batch_tracking" boxName="tracking_type"
                                                text="{{ __('item.batch_tracking') }}" value="batch" boxType="radio"
                                                parentDivClass="fw-bold" />
                                        @endif

                                        @if (app('company')['enable_serial_tracking'])
                                            <x-radio-block id="serial_tracking" boxName="tracking_type"
                                                text="{{ __('item.serial_no_tracking') }}" value="serial"
                                                boxType="radio" parentDivClass="fw-bold" />
                                        @endif

                                    </div>
                                </div>

                                <ul class="nav nav-tabs nav-success" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#successhome"
                                            role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class='bx bx-dollar font-18 me-1'></i>
                                                </div>
                                                <div class="tab-title">{{ __('item.pricing') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item item-type-product" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#successprofile" role="tab"
                                            aria-selected="false">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class='bx bx-box font-18 me-1'></i>
                                                </div>
                                                <div class="tab-title">{{ __('item.stock') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#successcontact" role="tab"
                                            aria-selected="false">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class='bx bx-image-add font-18 me-1'></i>
                                                </div>
                                                <div class="tab-title">{{ __('app.image') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content py-3">
                                    <div class="tab-pane fade show active" id="successhome" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-label for="purchase_price" name="{{ __('item.purchase_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="purchase_price" :required="false"
                                                        additionalClasses='cu_numeric'
                                                        value="{{ $formatNumber->formatWithPrecision($item->purchase_price, comma: false) }}" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax"
                                                        selected="{{ $item->is_purchase_price_with_tax }}"
                                                        dropdownName='is_purchase_price_with_tax' />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="tax_id" name="{{ __('tax.tax') }}" />
                                                <div class="input-group">
                                                    <x-drop-down-taxes selected="{{ $item->tax_id }}" />
                                                    <button type="button" class="btn btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#taxModal"><i
                                                            class="bx bx-plus-circle me-0"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="purchase_price"
                                                    name="{{ __('item.sale_profit_margin') }} (%)" />
                                                <x-input type="text" name="profit_margin" :required="false"
                                                    additionalClasses='cu_numeric'
                                                    value="{{ $formatNumber->formatWithPrecision($item->profit_margin, comma: false) }}" />
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-label for="sale_price" name="{{ __('item.sale_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="sale_price" :required="true"
                                                        additionalClasses='cu_numeric'
                                                        value="{{ $formatNumber->formatWithPrecision($item->sale_price, comma: false) }}" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax"
                                                        selected="{{ $item->is_sale_price_with_tax }}"
                                                        dropdownName='is_sale_price_with_tax' />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="wholesale_price" name="{{ __('item.wholesale_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="wholesale_price" :required="false"
                                                        additionalClasses='cu_numeric'
                                                        value="{{ $formatNumber->formatWithPrecision($item->wholesale_price, comma: false) }}" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax"
                                                        selected="{{ $item->is_wholesale_price_with_tax }}"
                                                        dropdownName='is_wholesale_price_with_tax' />
                                                </div>
                                            </div>
                                            {{-- Id company is enabled with discount then only show this else hide it --}}
                                            <div class="col-md-4 {{ app('company')['show_discount'] ? '' : 'd-none' }}">
                                                <x-label for="discount_on_sale"
                                                    name="{{ __('item.discount_on_sale') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="sale_price_discount" :required="false"
                                                        additionalClasses='cu_numeric'
                                                        value="{{ $formatNumber->formatWithPrecision($item->sale_price_discount, comma: false) }}" />
                                                    <x-dropdown-general optionNaming="amountOrPercentage"
                                                        selected="{{ $item->sale_price_discount_type }}"
                                                        dropdownName='sale_price_discount_type' />
                                                </div>
                                            </div>
                                            @if (app('company')['show_mrp'])
                                                <div class="col-md-2">
                                                    @php
                                                        $mrpToolTip =
                                                            '<span data-bs-toggle="tooltip" data-bs-placement="top" title="' .
                                                            __('item.maximum_retail_price') .
                                                            '">
                                                                        <i class="fadeIn animated bx bx-info-circle text-primary"></i>
                                                                    </span>';
                                                    @endphp
                                                    <x-label for="mrp"
                                                        name="{{ __('item.mrp') }} {!! $mrpToolTip !!}" />
                                                    <x-input type="text" name="mrp" :required="false"
                                                        additionalClasses='cu_numeric'
                                                        value="{{ $formatNumber->formatWithPrecision($item->mrp, comma: false) }}" />
                                                </div>
                                            @endif
                                            <div class="col-md-2">
                                                @php
                                                    $mspToolTip =
                                                        '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                                title="' .
                                                        __('item.minimum_selling_price') .
                                                        ': <br>' .
                                                        __('item.msp_message') .
                                                        '">
                                                                <i class="fadeIn animated bx bx-info-circle text-primary"></i>
                                                            </span>';

                                                @endphp
                                                <x-label for="msp"
                                                    name="{{ __('item.msp') }} {!! $mspToolTip !!}" />
                                                <x-input type="text" name="msp" :required="false"
                                                    additionalClasses='cu_numeric'
                                                    value="{{ $formatNumber->formatWithPrecision($item->msp, comma: false) }}" />
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane fade" id="successprofile" role="tabpanel">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-label for="warehouse_id" name="{{ __('warehouse.warehouse') }}" />
                                                <x-dropdown-warehouse
                                                    selected="{{ $transaction ? $transaction->warehouse_id : '' }}"
                                                    dropdownName='warehouse_id' :viewAllWarehouse=true />
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="transaction_date" name="{{ __('app.as_of_date') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" additionalClasses="datepicker-edit"
                                                        name="transaction_date" :required="true"
                                                        value="{{ $transaction ? $transaction->formatted_transaction_date : $todaysDate }}" />
                                                    <span class="input-group-text" id="input-near-focus"
                                                        role="button"><i
                                                            class="fadeIn animated bx bx-calendar-alt"></i></span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <x-label for="opening_quantity"
                                                    name="{{ __('item.opening_quantity') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" additionalClasses="cu_numeric"
                                                        name="opening_quantity" :required="false"
                                                        value="{{ $transaction ? $formatNumber->formatQuantity($transaction->quantity) : 0 }}" />

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="at_price" name="{{ __('item.at_price') }}" />
                                                <x-input type="text" additionalClasses="cu_numeric" name="at_price"
                                                    :required="false"
                                                    value="{{ $transaction ? $formatNumber->formatWithPrecision($transaction->unit_price, comma: false) : 0 }}" />
                                            </div>

                                        </div>


                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <x-label for="min_stock" name="{{ __('item.min_stock') }}" />
                                                <x-input type="text" additionalClasses="cu_numeric" name="min_stock"
                                                    :required="false"
                                                    value="{{ $formatNumber->formatQuantity($item->min_stock) }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="location" name="{{ __('item.item_location') }}" />
                                                <x-input type="text" name="item_location" :required="false"
                                                    value="{{ $item->item_location }}" />
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane fade" id="successcontact" role="tabpanel">
                                        <div class="col-md-12">
                                            <x-label for="picture" name="{{ __('app.image') }}" />
                                            <x-browse-image src="{{ url('/item/getimage/' . $item->image_path) }}"
                                                name='image' imageid='uploaded-image-1'
                                                inputBoxClass='input-box-class-1' imageResetClass='image-reset-class-1' />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!-- Import Modals -->
    @include('modals.tax.create')
    @include('modals.item.brand.create')
    @include('modals.item.category.create')
    @include('modals.item.serial-tracking')
    @include('modals.item.batch-tracking')

@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/items/item.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/serial-tracking.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/batch-tracking.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/tax/tax.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/item/brand/brand.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/item/category/category.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/unit/unit.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/item-edit.js') }}"></script>
    <script type="text/javascript">
        var _baseUnitId = {{ $item->base_unit_id }};
        var _secondaryUnitId = {{ $item->secondary_unit_id }};
        var _conversionRate = {{ $formatNumber->formatWithPrecision($item->conversion_rate, comma: false) }};
        var _trackingType = '{{ $item->tracking_type }}';
    </script>
@endsection
