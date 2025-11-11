@extends('layouts.app')
@section('title', __('app.company'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">

            <x-breadcrumb :langArray="['app.settings', 'app.company']" />

            <div class="row">

                <div class="col-12 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="my-3">{{ __('app.company_settings') }}</h5>
                            <div class="fm-menu">
                                <div class="list-group list-group-flush">
                                    <a href="javascript:;" class="list-group-item py-1 active text-white show_company"><i
                                            class='bx bx-store me-2'></i><span>{{ __('app.company') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_prefix"><i
                                            class='bx bx-label me-2'></i><span>{{ __('app.prefix_codes') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_general"><i
                                            class='bx bx-folder me-2'></i><span>{{ __('app.general') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_item"><i
                                            class='bx bx-package me-2'></i><span>{{ __('item.item') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_print"><i
                                            class='bx bx-printer me-2'></i><span>{{ __('app.print') }}</span></a>
                                    <a href="javascript:;" class="list-group-item py-1 show_module"><i
                                            class='bx bx-cube me-2'></i><span>{{ __('app.modules') }}</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <!--Tab: Company -->
                    <div class="card company_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.company') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="companyForm" action="{{ route('company.update') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <input type="hidden" name='id' value="{{ $company->id }}" />

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true"
                                        value="{{ $company->name }}" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="mobile" name="{{ __('app.mobile') }}" />
                                    <x-input type="number" name="mobile" :required="true"
                                        value="{{ $company->mobile }}" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="email" name="{{ __('app.email') }}" />
                                    <x-input type="email" name="email" :required="true"
                                        value="{{ $company->email }}" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="tax_number" name="{{ __('tax.tax_number') }}" />
                                    <x-input type="text" name="tax_number" :required="false"
                                        value="{{ $company->tax_number }}" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="address" name="{{ __('app.address') }}" />
                                    <x-textarea name="address" value="{{ $company->address }}" textRows="5" />
                                </div>
                                @if (app('company')['tax_type'] == 'gst')
                                    <div class="col-md-12">
                                        <x-label for="state_id" name="{{ __('app.state_name') }}" />
                                        <x-dropdown-states selected="{{ $company->state_id }}" dropdownName='state_id' />
                                    </div>
                                @endif
                                <div class="col-md-12 d-none">
                                    <x-label for="bank_details" name="{{ __('app.bank_details') }}" />
                                    <x-textarea name="bank_details" value="{{ $company->bank_details }}" />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="company_logo" name="{{ __('app.company_logo') }}" />
                                    <x-browse-image src="{{ url('/company/getimage/' . $company->colored_logo) }}"
                                        name="colored_logo" imageid="uploaded-image-1" inputBoxClass="input-box-class-1"
                                        imageResetClass="image-reset-class-1" />

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
                    <!--Tab End: Company -->
                    <!--Tab: Prefix -->
                    <div class="card prefix_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.prefix_codes') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="prefixForm" action="{{ route('prefix.update') }}"
                                enctype="multipart/form-data" method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <div class="col-md-12 d-none">
                                    <x-label for="order" name="{{ __('order.orders') }}" />
                                    <x-input type="text" name="order" value="{{ $prefix->order }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12 d-none">
                                    <x-label for="job_code" name="{{ __('schedule.job_code') }}" />
                                    <x-input type="text" name="job_code" value="{{ $prefix->job_code }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="expense" name="{{ __('expense.expense') }}" />
                                    <x-input type="text" name="expense" value="{{ $prefix->expense }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="purchase_order" name="{{ __('purchase.order.order') }}" />
                                    <x-input type="text" name="purchase_order"
                                        value="{{ $prefix->purchase_order }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="purchase_bill" name="{{ __('purchase.bill') }}" />
                                    <x-input type="text" name="purchase_bill" value="{{ $prefix->purchase_bill }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="purchase_return" name="{{ __('purchase.return.return') }}" />
                                    <x-input type="text" name="purchase_return"
                                        value="{{ $prefix->purchase_return }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="sale_order" name="{{ __('sale.order.order') }}" />
                                    <x-input type="text" name="sale_order" value="{{ $prefix->sale_order }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="sale" name="{{ __('sale.invoice') }}" />
                                    <x-input type="text" name="sale" value="{{ $prefix->sale }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="sale_return" name="{{ __('sale.return.return') }}" />
                                    <x-input type="text" name="sale_return" value="{{ $prefix->sale_return }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="stock_transfer" name="{{ __('warehouse.stock_transfer') }}" />
                                    <x-input type="text" name="stock_transfer"
                                        value="{{ $prefix->stock_transfer }}" />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="quotation" name="{{ __('sale.quotation.quotation') }}" />
                                    <x-input type="text" name="quotation" value="{{ $prefix->quotation }}" />
                                    <div class="valid-feedback"></div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="smtpSubmit" class="primary px-4"
                                            text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: Prefix -->
                    <!--Tab: General -->
                    <div class="card general_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.general_settings') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="generalForm"
                                action="{{ route('company.general.update') }}" enctype="multipart/form-data"
                                method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->show_discount ? 'checked' : '' }} id="show_discount"
                                            name="show_discount">
                                        <label class="form-check-label" for="show_discount">
                                            {{ __('app.show_discount') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->allow_negative_stock_billing ? 'checked' : '' }}
                                            id="allow_negative_stock_billing" name="allow_negative_stock_billing">
                                        <label class="form-check-label" for="allow_negative_stock_billing">
                                            {{ __('app.allow_negative_stock_billing') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_minimum_stock_qty"
                                            name="enable_minimum_stock_qty"
                                            {{ $company->enable_minimum_stock_qty ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_minimum_stock_qty">
                                            {{ __('app.enable_minimum_stock_qty') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <x-label for="minimum_stock_qty" name="{{ __('app.minimum_stock_quantity') }}" />
                                    <x-input type="number" name="minimum_stock_qty"
                                        value="{{ $company->minimum_stock_qty ?? '' }}" min="0"
                                        placeholder="{{ __('app.minimum_stock_quantity') }}" />
                                </div>


                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->is_enable_secondary_currency ? 'checked' : '' }}
                                            id="is_enable_secondary_currency" name="is_enable_secondary_currency">
                                        <label class="form-check-label" for="is_enable_secondary_currency">
                                            {{ __('currency.is_enable_secondary_currency') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->is_enable_carrier_charge ? 'checked' : '' }}
                                            id="is_enable_carrier_charge" name="is_enable_carrier_charge">
                                        <label class="form-check-label" for="is_enable_carrier_charge">
                                            {{ __('carrier.is_enable_carrier_charge') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <x-label for="number_precision" name="{{ __('app.number_precision') }}" />
                                    <x-dropdown-precision-format selected="{{ $company->number_precision }}"
                                        precisionFor='number' selectionBoxName='number_precision' />
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="quantity_precision" name="{{ __('app.quantity_precision') }}" />
                                    <x-dropdown-precision-format selected="{{ $company->quantity_precision }}"
                                        precisionFor='quntity' selectionBoxName='quantity_precision' />
                                    <div class="valid-feedback"></div>
                                </div>


                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="generalSubmit" class="primary px-4"
                                            text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: General -->
                    <!--Tab: Item -->
                    <form class="needs-validation" id="itemForm" action="{{ route('company.item.update') }}"
                        enctype="multipart/form-data" method="post">
                        {{-- CSRF Protection --}}
                        @csrf
                        @method('POST')
                        <div class="card item_tab">
                            <div class="card-header px-4 py-3">
                                <h5 class="mb-0">{{ __('item.item_settings') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->is_item_name_unique ? 'checked' : '' }}
                                                id="is_item_name_unique" name="is_item_name_unique">
                                            <label class="form-check-label" for="is_item_name_unique">
                                                {{ __('item.is_item_name_unique') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->show_hsn ? 'checked' : '' }} id="show_hsn" name="show_hsn">
                                            <label class="form-check-label" for="show_hsn">
                                                {{ __('item.show_hsn') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->show_sku ? 'checked' : '' }} id="show_sku" name="show_sku">
                                            <label class="form-check-label" for="show_sku">
                                                {{ __('item.show_sku') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->show_mrp ? 'checked' : '' }} id="show_mrp" name="show_mrp">
                                            <label class="form-check-label" for="show_mrp">
                                                {{ __('item.show_mrp') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->restrict_to_sell_above_mrp ? 'checked' : '' }}
                                                id="restrict_to_sell_above_mrp" name="restrict_to_sell_above_mrp">
                                            <label class="form-check-label" for="restrict_to_sell_above_mrp">
                                                {{ __('item.restrict_to_sell_above_mrp') }} <span class="text-danger"><i
                                                        class="fadeIn animated bx bx-arrow-to-top"></i></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->restrict_to_sell_below_msp ? 'checked' : '' }}
                                                id="restrict_to_sell_below_msp" name="restrict_to_sell_below_msp">
                                            <label class="form-check-label" for="restrict_to_sell_below_msp">
                                                {{ __('item.restrict_to_sell_below_msp') }} <span class="text-danger"><i
                                                        class="fadeIn animated bx bx-arrow-to-bottom"></i></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->auto_update_sale_price ? 'checked' : '' }}
                                                id="auto_update_sale_price" name="auto_update_sale_price">
                                            <label class="form-check-label" for="auto_update_sale_price">
                                                {{ __('item.auto_update_sale_price') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->auto_update_purchase_price ? 'checked' : '' }}
                                                id="auto_update_purchase_price" name="auto_update_purchase_price">
                                            <label class="form-check-label" for="auto_update_purchase_price">
                                                {{ __('item.auto_update_purchase_price') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="rounded border p-3 bg-light">
                                            <span>{{ __('item.auto_update_average_purchase_price') }}</span>
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <x-radio-block id="yes_1" boxName="auto_update_average_purchase_price"
                                                    text="{{ __('app.yes') }}" value="yes" boxType="radio"
                                                    parentDivClass="" :checked="$company->auto_update_average_purchase_price" />
                                                <x-radio-block id="no_1" boxName="auto_update_average_purchase_price"
                                                    text="{{ __('app.no') }}" value="no" boxType="radio"
                                                    parentDivClass="" :checked="!$company->auto_update_average_purchase_price" />
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <x-label for="tax_type" name="{{ __('tax.tax_type') }}" />
                                        <select class="form-select" name="tax_type" data-placeholder="Choose one thing">
                                            <option value="tax" {{ $company->tax_type == 'tax' ? 'selected' : '' }}>
                                                Enable Tax</option>
                                            <option value="gst" {{ $company->tax_type == 'gst' ? 'selected' : '' }}>
                                                Enable GST</option>
                                            <option value="no-tax"
                                                {{ $company->tax_type == 'no-tax' ? 'selected' : '' }}>
                                                No Tax</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-header px-4 py-3">
                                <h5 class="mb-0">{{ __('item.batch_and_tracking') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_serial_tracking ? 'checked' : '' }}
                                                id="enable_serial_tracking" name="enable_serial_tracking">
                                            <label class="form-check-label" for="enable_serial_tracking">
                                                {{ __('item.enable_serial_tracking') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_batch_tracking ? 'checked' : '' }}
                                                id="enable_batch_tracking" name="enable_batch_tracking">
                                            <label class="form-check-label" for="enable_batch_tracking">
                                                {{ __('item.enable_batch_tracking') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="rounded border p-3 bg-light">
                                            <span>{{ __('item.is_batch_compulsory') }}</span>
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <x-radio-block id="yes" boxName="is_batch_compulsory"
                                                    text="{{ __('app.yes') }}" value="yes" boxType="radio"
                                                    parentDivClass="" :checked="$company->is_batch_compulsory" />
                                                <x-radio-block id="no" boxName="is_batch_compulsory"
                                                    text="{{ __('app.no') }}" value="no" boxType="radio"
                                                    parentDivClass="" :checked="!$company->is_batch_compulsory" />
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_mfg_date ? 'checked' : '' }} id="enable_mfg_date"
                                                name="enable_mfg_date">
                                            <label class="form-check-label" for="enable_mfg_date">
                                                {{ __('item.enable_mfg_date') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_exp_date ? 'checked' : '' }} id="enable_exp_date"
                                                name="enable_exp_date">
                                            <label class="form-check-label" for="enable_exp_date">
                                                {{ __('item.enable_exp_date') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_color ? 'checked' : '' }} id="enable_color"
                                                name="enable_color">
                                            <label class="form-check-label" for="enable_color">
                                                {{ __('item.enable_color') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_model ? 'checked' : '' }} id="enable_model"
                                                name="enable_model">
                                            <label class="form-check-label" for="enable_model">
                                                {{ __('item.enable_model') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->enable_size ? 'checked' : '' }} id="enable_size"
                                                name="enable_size">
                                            <label class="form-check-label" for="enable_size">
                                                {{ __('item.enable_size') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                            <x-button type="submit" buttonId="itemSubmit" class="primary px-4"
                                                text="{{ __('app.submit') }}" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                                class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Tab End: Item -->
                    </form>

                    <!--Tab: Print -->
                    <div class="card print_tab">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.print_settings') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="printForm"
                                action="{{ route('company.print.update') }}" enctype="multipart/form-data"
                                method="post">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->show_tax_summary ? 'checked' : '' }} id="show_tax_summary"
                                            name="show_tax_summary">
                                        <label class="form-check-label" for="show_tax_summary">
                                            {{ __('app.show_tax_summary') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->enable_print_tax ? 'checked' : '' }} id="enable_print_tax"
                                            name="enable_print_tax">
                                        <label class="form-check-label"
                                            for="enable_print_tax">{{ __('app.enable_print_tax') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->enable_print_discount ? 'checked' : '' }}
                                            id="enable_print_discount" name="enable_print_discount">
                                        <label class="form-check-label"
                                            for="enable_print_discount">{{ __('app.enable_print_discount') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->show_signature_on_invoice ? 'checked' : '' }}
                                            id="show_signature_on_invoice" name="show_signature_on_invoice">
                                        <label class="form-check-label" for="show_signature_on_invoice">
                                            {{ __('app.show_signature_on_invoice') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->show_party_due_payment ? 'checked' : '' }}
                                            id="show_party_due_payment" name="show_party_due_payment">
                                        <label class="form-check-label" for="show_party_due_payment">
                                            {{ __('app.show_due_payment_on_invoice_or_bill') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ $company->show_terms_and_conditions_on_invoice ? 'checked' : '' }}
                                            id="show_terms_and_conditions_on_invoice"
                                            name="show_terms_and_conditions_on_invoice">
                                        <label class="form-check-label" for="show_terms_and_conditions_on_invoice">
                                            {{ __('app.show_terms_and_conditions_on_invoice') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="terms_and_conditions" name="{{ __('app.terms_and_conditions') }}" />
                                    <x-textarea name="terms_and_conditions" value="{{ $company->terms_and_conditions }}"
                                        textRows="5" />

                                </div>

                                <div class="col-md-12">
                                    <x-label for="signature" name="{{ __('app.signature') }}" />
                                    <x-browse-image src="{{ url('/company/signature/getimage/' . $company->signature) }}"
                                        name="signature" imageid="uploaded-image-2" inputBoxClass="input-box-class-2"
                                        imageResetClass="image-reset-class-2" />

                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" buttonId="generalSubmit" class="primary px-4"
                                            text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Tab End: Print -->
                    <!--Tab: Modules -->
                    <form class="needs-validation" id="moduleForm" action="{{ route('company.module.update') }}"
                        enctype="multipart/form-data" method="post">
                        {{-- CSRF Protection --}}
                        @csrf
                        @method('POST')
                        <div class="card module_tab">
                            <div class="card-header px-4 py-3">
                                <h5 class="mb-0">{{ __('app.modules') }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-12 d-none">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->is_enable_crm ? 'checked' : '' }} id="is_enable_crm"
                                                name="is_enable_crm">
                                            <label class="form-check-label" for="is_enable_crm">
                                                {{ __('app.enable_crm') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $company->is_enable_carrier ? 'checked' : '' }} id="is_enable_carrier"
                                                name="is_enable_carrier">
                                            <label class="form-check-label" for="is_enable_carrier">
                                                {{ __('carrier.enable_carrier') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                            <x-button type="submit" buttonId="moduleSubmit" class="primary px-4"
                                                text="{{ __('app.submit') }}" />
                                            <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                                class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--Tab End: Modules -->
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ versionedAsset('custom/js/company/edit.js') }}"></script>
@endsection
