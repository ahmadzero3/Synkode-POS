@extends('layouts.app')
@section('title', __('payment.close_cash_list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'payment.cash_and_bank',
            'payment.close_cash_list',
        ]" />

            <div class="card">

                <div class="card-header px-4 py-3 d-flex align-items-center">
                    <!-- Left side content -->
                    <div>
                        <h5 class="mb-0 text-uppercase">
                            {{ __('payment.close_cash_list') }} ({{ \Carbon\Carbon::now()->format('d-m-Y') }}) :
                        </h5>
                    </div>
                </div>


                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <x-label for="from_date" name="{{ __('app.from_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="Filter From Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="from_date" :required="true"
                                    value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <x-label for="to_date" name="{{ __('app.to_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="Filter To Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true"
                                    value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>
                    <form class="row g-3 needs-validation" id="datatableForm"
                        action="{{ route('cash.transaction.delete') }}" enctype="multipart/form-data">
                        {{-- CSRF Protection --}}
                        @csrf
                        @method('POST')
                        <input type="hidden" id="base_url" value="{{ url('/') }}">
                        <input type="hidden" id="payment_for" value="sale-invoice">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"></th>
                                        <th>{{ __('app.opening_balance') }}</th>
                                        <th>{{ __('app.today_income') }}</th>
                                        <th>{{ __('app.total_income') }}</th>
                                        <th>{{ __('app.today_expense') }}</th>
                                        <th>{{ __('app.balance') }}</th>
                                        <th>{{ __('app.created_by') }}</th>
                                        <th>{{ __('app.created_at') }}</th>
                                        <th>{{ __('app.update_at') }}</th>
                                        <th>{{ __('app.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/transaction/list-close-cash.js') }}"></script>
@endsection