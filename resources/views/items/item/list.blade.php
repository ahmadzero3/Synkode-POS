@extends('layouts.app')
@section('title', __('item.list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.items', 'item.list']" />

            <div class="card">

                <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                    <!-- Other content on the left side -->
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('item.list') }}</h5>
                    </div>
                    <div class="d-flex gap-2">
                        @can('import.item')
                            <!-- Button pushed to the right side -->
                            <x-anchor-tag href="{{ route('import.items') }}" text="{{ __('app.import') }}"
                                class="btn btn-outline-primary px-5" />
                        @endcan

                        @can('item.create')
                            <!-- Button pushed to the right side -->
                            <x-anchor-tag href="{{ route('item.create') }}" text="{{ __('item.create') }}"
                                class="btn btn-primary px-5" />
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <x-label for="is_service" name="{{ __('item.item_type') }}" />
                            <select class="form-select single-select-clear-field" id="is_service" name="is_service"
                                data-placeholder="Choose one thing">
                                <option></option>
                                <option value='0'>{{ __('item.product') }}</option>
                                <option value='1'>{{ __('service.service') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <x-label for="brand_id" name="{{ __('item.brand.brand') }}" />
                            <x-dropdown-brand selected="" :showSelectOptionAll='true' />
                        </div>
                        <div class="col-md-4">
                            <x-label for="item_category_id" name="{{ __('item.category.category') }}" />
                            <x-dropdown-item-category selected="" :isMultiple='false' :showSelectOptionAll='true' />
                        </div>
                        <div class="col-md-4">
                            <x-label for="user_id" name="{{ __('user.user') }}" />
                            <x-dropdown-user selected="" :showOnlyUsername='true' />
                        </div>
                        <div class="col-md-4">
                            <x-label for="warehouse_id" name="{{ __('warehouse.warehouse_stock') }}" />
                            <x-dropdown-warehouse selected="" dropdownName='warehouse_id' :showSelectOptionAll='true' />
                        </div>
                        <div class="col-md-4">
                            <x-label for="tracking_type" name="{{ __('item.tracking_type') }}" />
                            <select class="form-select single-select-clear-field" id="tracking_type" name="tracking_type"
                                data-placeholder="Choose one thing">
                                <option></option>
                                <option value='regular'>{{ __('item.regular') }}</option>
                                <option value='batch'>{{ __('item.batch_tracking') }}</option>
                                <option value='serial'>{{ __('item.serial_no_tracking') }}</option>
                            </select>
                        </div>
                    </div>
                    <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('item.delete') }}"
                        enctype="multipart/form-data">
                        {{-- CSRF Protection --}}
                        @csrf
                        @method('POST')
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                                        <th><input class="form-check-input row-select" type="checkbox"></th>
                                        <th>{{ __('app.name') }}</th>
                                        <th>{{ __('item.code') }}</th>
                                        <th class="{{ !app('company')['show_sku'] ? 'd-none' : '' }}">{{ __('item.sku') }}
                                        </th>
                                        <th>{{ __('item.brand.brand') }}</th>
                                        <th>{{ __('item.category.category') }}</th>
                                        <th>{{ __('item.sale_price') }}</th>
                                        <th>{{ __('item.purchase_price') }}</th>
                                        <th>{{ __('item.quantity') }}</th>
                                        <th>{{ __('item.tracking_type') }}</th>
                                        <th>{{ __('app.created_by') }}</th>
                                        <th>{{ __('app.created_at') }}</th>
                                        <th>{{ __('app.action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end row-->
    </div>
    </div>
@endsection
@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/items/item-list.js') }}"></script>
    <script>
        window.enableMinimumStockQty = {{ !empty(app('company')['enable_minimum_stock_qty']) ? 'true' : 'false' }};
        window.minimumStockQty = {{ app('company')['minimum_stock_qty'] ?? 0 }};
    </script>
@endsection
