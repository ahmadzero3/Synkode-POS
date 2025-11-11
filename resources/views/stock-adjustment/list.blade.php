@extends('layouts.app')
@section('title', __('warehouse.stock_adjustment_list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['item.stock', 'warehouse.stock_adjustment_list']" />

            <div class="card">

                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <!-- Other content on the left side -->
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('warehouse.stock_adjustment_list') }}</h5>
                    </div>

                    @can('stock_adjustment.create')
                        <!-- Button pushed to the right side -->
                        <x-anchor-tag href="{{ route('stock_adjustment.create') }}" text="{{ __('warehouse.new_adjustment') }}"
                            class="btn btn-primary px-5" />
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <x-label for="from_date" name="{{ __('app.from_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="from_date"
                                    :required="true" value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <x-label for="to_date" name="{{ __('app.to_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true"
                                    value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <x-label for="user_id" name="{{ __('user.user') }}" />
                            <x-dropdown-user selected="" :showOnlyUsername='true' :canViewAllUsers="auth()->user()->can('stock_adjustment.can.view.other.users.stock.adjustments')" />
                        </div>
                    </div>
                    <form class="row g-3 needs-validation" id="datatableForm"
                        action="{{ route('stock_adjustment.delete') }}" enctype="multipart/form-data">
                        {{-- CSRF Protection --}}
                        @csrf
                        @method('POST')
                        <input type="hidden" id="base_url" value="{{ url('/') }}">

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                                        <th><input class="form-check-input row-select" type="checkbox"></th>
                                        <th>{{ __('warehouse.adjustment_code') }}</th>
                                        <th>{{ __('app.date') }}</th>
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
    <script src="{{ versionedAsset('custom/js/stock-adjustment/stock-adjustment-list.js') }}"></script>
@endsection
