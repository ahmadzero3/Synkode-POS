@extends('layouts.app')
@section('title', __('item.create'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.items', 'item.list', 'item.create']" />
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('item.details') }}</h5>
                            <div class="btn-group">
                                <input type="radio" class="btn-check" name="item_type_radio" id="product" value="product"
                                    autocomplete="off" checked>
                                <label class="btn btn-outline-primary btn-sm"
                                    for="product">{{ __('item.product') }}</label>

                                <input type="radio" class="btn-check" name="item_type_radio" id="service"
                                    value="service" autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm"
                                    for="service">{{ __('service.service') }}</label>

                                <input type="radio" class="btn-check" name="item_type_radio" id="offers" value="offers"
                                    autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm" for="offers">{{ __('item.offers') }}</label>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="itemForm" action="{{ route('item.store') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                {{-- Units Modal --}}
                                @include('modals.unit.create')

                                <input type="hidden" id="operation" name="operation" value="save">
                                <input type="hidden" id="base_url" value="{{ url('/') }}">
                                <input type="hidden" name="serial_number_json" value=''>
                                <input type="hidden" name="batch_details_json" value=''>
                                <input type="hidden" name="is_service" value='0'>
                                <input type="hidden" name="offer_items_json" value=''>

                                <div class="col-md-4">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="" />
                                </div>
                                @if (app('company')['show_hsn'])
                                    <div class="col-md-4">
                                        <x-label for="hsn" name="{{ __('item.hsn') }}" />
                                        <x-input type="text" name="hsn" :required="false" value="" />
                                    </div>
                                @endif
                                @if (app('company')['show_sku'])
                                    <div class="col-md-4">
                                        <x-label for="sku" name="{{ __('item.sku') }}" />
                                        <x-input type="text" name="sku" :required="false" value="" />
                                    </div>
                                @endif

                                <div class="col-md-4">
                                    <x-label for="brand_id" name="{{ __('item.brand.brand') }}" />
                                    <div class="input-group">
                                        <x-dropdown-brand selected="" :showSelectOptionAll=true />
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#brandModal"><i class="bx bx-plus-circle me-0"></i>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="hsn" name="{{ __('item.code') }}" />
                                    <div class="input-group mb-3">
                                        <x-input type="text" name="item_code" :required="true"
                                            value="{{ $data['count_id'] }}" />
                                        <button class="btn btn-outline-secondary auto-generate-code" type="button"
                                            id="button-addon2">{{ __('app.auto') }}</button>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="item_category_id" name="{{ __('item.category.category') }}" />
                                    <div class="input-group">
                                        <x-dropdown-item-category selected="" :isMultiple=false />
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#itemCategoryModal"><i class="bx bx-plus-circle me-0"></i>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <x-label for="description" name="{{ __('app.description') }}" />
                                    <x-textarea name="description" value="" />
                                </div>

                                <!-- Offer item picker -->
                                <div class="col-12 item-type-offers" style="display:none;">
                                    <x-label for="search_item" name="{{ __('item.enter_item_name') }}" />
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">
                                            <i class="fadeIn animated bx bx-barcode-reader text-primary"></i>
                                        </span>
                                        <input type="text" id="search_item" value="" class="form-control"
                                            placeholder="Scan Barcode/Search Items">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#itemModal">
                                            <i class="bx bx-plus-circle me-0"></i>
                                        </button>
                                    </div>

                                    <div class="col-md-12 table-responsive mt-3">
                                        <table class="table mb-0 table-striped table-bordered" id="offersItemsTable">
                                            <thead>
                                                <tr class="text-uppercase">
                                                    <th scope="col">{{ __('item.item') }}</th>
                                                    <th scope="col">{{ __('app.qty') }}</th>
                                                    <th scope="col">{{ __('app.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="3"
                                                        class="text-center fw-light fst-italic default-row">
                                                        No items are added yet!!
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-4 p-4 item-type-product">
                                    <button type="button" class="btn btn-light px-5 rounded-0" data-bs-toggle="modal"
                                        data-bs-target="#unitModal">{{ __('unit.select_unit') }}</button>
                                    <label class="primary unit-label"></label>
                                </div>

                                <div class="col-md-4 d-none">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="" dropdownName='status' />
                                </div>

                                <div class="col-md-12 mb-3 item-type-product">
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
                                                <div class="tab-icon"><i class='bx bx-dollar font-18 me-1'></i></div>
                                                <div class="tab-title">{{ __('item.pricing') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#successprofile" role="tab"
                                            aria-selected="false">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class='bx bx-box font-18 me-1'></i></div>
                                                <div class="tab-title">{{ __('item.stock') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" data-bs-toggle="tab" href="#successcontact" role="tab"
                                            aria-selected="false">
                                            <div class="d-flex align-items-center">
                                                <div class="tab-icon"><i class='bx bx-image-add font-18 me-1'></i></div>
                                                <div class="tab-title">{{ __('app.image') }}</div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content py-3">
                                    <!-- Keep this pane for all types; we’ll hide only purchase field via JS -->
                                    <div class="tab-pane fade show active item-type-product-service" id="successhome"
                                        role="tabpanel">
                                        <div class="row">
                                            <!-- 👇 Hook class added to only hide this when Offers is selected -->
                                            <div class="col-md-4 purchase-field">
                                                <x-label for="purchase_price" name="{{ __('item.purchase_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="purchase_price" :required="false"
                                                        additionalClasses='cu_numeric' value="" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax" selected=""
                                                        dropdownName='is_purchase_price_with_tax' />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <x-label for="tax_id" name="{{ __('tax.tax') }}" />
                                                <div class="input-group">
                                                    <x-drop-down-taxes selected="" />
                                                    <button type="button" class="btn btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#taxModal"><i
                                                            class="bx bx-plus-circle me-0"></i>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <x-label for="purchase_price"
                                                    name="{{ __('item.sale_profit_margin') }} (%)" />
                                                <x-input type="text" name="profit_margin" :required="false"
                                                    additionalClasses='cu_numeric' value="" />
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-label for="sale_price" name="{{ __('item.sale_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="sale_price" :required="true"
                                                        additionalClasses='cu_numeric' value="0" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax" selected=""
                                                        dropdownName='is_sale_price_with_tax' />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="wholesale_price" name="{{ __('item.wholesale_price') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="wholesale_price" :required="true"
                                                        additionalClasses='cu_numeric' value="0" />
                                                    <x-dropdown-general optionNaming="withOrWithoutTax" selected=""
                                                        dropdownName='is_wholesale_price_with_tax' />
                                                </div>
                                            </div>

                                            <div class="col-md-4 {{ app('company')['show_discount'] ? '' : 'd-none' }}">
                                                <x-label for="discount_on_sale"
                                                    name="{{ __('item.discount_on_sale') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" name="sale_price_discount" :required="false"
                                                        additionalClasses='cu_numeric' value="" />
                                                    <x-dropdown-general optionNaming="amountOrPercentage" selected=""
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
                                                        additionalClasses='cu_numeric' value="" />
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
                                                    additionalClasses='cu_numeric' value="" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="successprofile" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-label for="warehouse_id" name="{{ __('warehouse.warehouse') }}" />
                                                <x-dropdown-warehouse selected="" dropdownName='warehouse_id' />
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="transaction_date" name="{{ __('app.as_of_date') }}" />
                                                <div class="input-group mb-3">
                                                    <x-input type="text" additionalClasses="datepicker"
                                                        name="transaction_date" :required="true" value="" />
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
                                                        name="opening_quantity" :required="false" value="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="at_price" name="{{ __('item.at_price') }}" />
                                                <x-input type="text" additionalClasses="cu_numeric" name="at_price"
                                                    :required="false" value="" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <x-label for="min_stock" name="{{ __('item.min_stock') }}" />
                                                <x-input type="text" additionalClasses="cu_numeric" name="min_stock"
                                                    :required="false" value="" />
                                            </div>
                                            <div class="col-md-4">
                                                <x-label for="location" name="{{ __('item.item_location') }}" />
                                                <x-input type="text" name="item_location" :required="false"
                                                    value="" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="successcontact" role="tabpanel">
                                        <div class="col-md-12">
                                            <x-label for="picture" name="{{ __('app.image') }}" />
                                            <x-browse-image src="{{ url('/noimage/') }}" name='image'
                                                imageid='uploaded-image-1' inputBoxClass='input-box-class-1'
                                                imageResetClass='image-reset-class-1' />
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
@endsection
