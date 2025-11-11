@extends('layouts.app')
@section('title', __('payment.close_cash'))

@section('css')
    <link rel="stylesheet" href="{{ versionedAsset('custom/css/transaction/close-cash.css') }}" />
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-timepicker.min.css') }}" />
@endsection

@section('content')

    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['payment.cash_and_bank', 'app.close_cash']" />

            <input type="hidden" id="base_url" value="{{ url('/') }}">
            <input type="hidden" id="created_by" value="{{ auth()->user()->id }}">
            <input type="hidden" id="user_id" value="{{ $filteredUserId ?? '' }}">
            <input type="hidden" id="has_register" value="{{ $restrictToUser ?? false ? 1 : 0 }}">

            <!-----Card One------>
            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                    <!-- Other content on the left side -->
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-uppercase">{{ __('app.close_cash') }}
                            ({{ \Carbon\Carbon::now()->format('d-m-Y') }})</h5>

                    </div>
                    <!-- Icon on the right side -->
                    <a href="{{ route('transaction.close-cash-print') }}" target="_blank">
                        <i class='bx bx-printer icon-large'></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3 d-flex justify-content-between">
                        <!-- From Date Left Side-->
                        <div class="col-md-3">
                            <x-label for="from_date" name="{{ __('app.from_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="{{ __('app.from_date') }}"><i
                                    class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="from_date"
                                    :required="true" value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>

                        <!-- To Date Right Side-->
                        <div class="col-md-3">
                            <x-label for="to_date" name="{{ __('app.to_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="{{ __('app.to_date') }}"><i
                                    class="fadeIn animated bx bx-info-circle"></i></a>
                            <div class="input-group mb-3">
                                <x-input type="text" additionalClasses="datepicker-edit" name="to_date" :required="true"
                                    value="" />
                                <span class="input-group-text" id="input-near-focus" role="button"><i
                                        class="fadeIn animated bx bx-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Opening Balance Below Dates -->
                    <div class="row g-3">
                        <div class="col-md-3 mx-auto">
                            <x-label for="opening_balance" name="{{ __('payment.opening_balance') }}" />
                            <div class="input-group mb-3">
                                <x-input type="text" name="opening_balance" :required="true"
                                    value="{{ number_format($openingBalance, 2) }}" readonly />
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                    <input type="hidden" id="created_by" value="{{ auth()->user()->id }}">
                    <input type="hidden" id="user_id" value="{{ $filteredUserId ?? '' }}">
                    <input type="hidden" id="has_register" value="{{ $restrictToUser ?? false ? 1 : 0 }}">
                </div>
            </div>

            <!-----Card Two------>
            <div class="card">
                <div class="card-header position-relative px-4 py-3 d-flex justify-content-between align-items-center">
                    {{-- left title --}}
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-uppercase">{{ __('app.income') }}</h5>
                    </div>

                    {{-- collapse/expand toggle button, starts as “minus” --}}
                    <button type="button" class="collapse-btn btn btn-light shadow-sm p-0">
                        <i class="bx bx-minus close-button"></i>
                    </button>

                    {{-- right title --}}
                    <h5 class="mb-0 text-uppercase">{{ __('app.expense') }}</h5>
                </div>

                <div class="card-body">
                    <div class="d-flex">

                        <!-- Table One -->
                        <div class="col-md-6 custom-col-45">
                            <form id="saleInvoiceForm" class="row g-3 needs-validation"
                                action="{{ route('sale.invoice.delete') }}">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')
                                <div class="table-responsive">
                                    <table id="saleInvoiceTable" class="table table-striped table-bordered border w-100">
                                        <thead>
                                            <tr>
                                                <th class="d-none"></th>
                                                <th><input class="form-check-input row-select" type="checkbox"></th>
                                                <th>{{ __('sale.code') }}</th>
                                                <th>{{ __('app.date') }}</th>
                                                <th>{{ __('app.total') }}</th>
                                                <th>{{ __('payment.balance') }}</th>
                                                <th>{{ __('app.created_by') }}</th>
                                                <th>{{ __('app.created_at') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- DataTables will populate here --}}
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>

                        <!-- Separator -->
                        <div class="separator"></div>

                        <!-- Table Two -->
                        <div class="col-md-6 custom-col-45 move-down">
                            <form id="expenseForm" class="row g-3 needs-validation"
                                action="{{ route('expense.delete') }}">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')
                                <div class="table-responsive">
                                    <table id="expenseTable" class="table table-striped table-bordered border w-100">
                                        <thead>
                                            <tr>
                                                <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                                                <th><input class="form-check-input row-select" type="checkbox"></th>
                                                <th>ex.p</th>
                                                <th>{{ __('app.date') }}</th>
                                                <th>{{ __('expense.category.category') }}</th>
                                                <th>{{ __('payment.amount') }}</th>
                                                <th>p.t type</th>
                                                <th>{{ __('app.created_by') }}</th>
                                                <th>{{ __('app.created_at') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>

            <!-----Card Three------>
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20">
                        <div class="col-md-6 offset-md-3 mb-20">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped no-stripe mb-0 table-three">
                                    <tbody>
                                        <tr class="table-3-tr-1">
                                            <td class="w-50 bg-gray text-right tr-p">{{ __('payment.opening_balance') }}
                                            </td>
                                            <td class="w-50 bg-gray text-right tr-p">
                                                {{ number_format($openingBalance, 2) }}</td>
                                        </tr>
                                        <tr class="table-3-tr-2">
                                            <td class="w-50 bg-gray text-right tr-p">{{ __('payment.today_income') }}</td>
                                            <td class="w-50 bg-gray text-right tr-p">{{ number_format($todayIncome, 2) }}
                                            </td>
                                        </tr>
                                        <tr class="bg-green">
                                            <td class="w-50 text-right tr-p">{{ __('payment.total_income') }}</td>
                                            <td class="w-50 text-right tr-p">{{ number_format($totalIncome, 2) }}</td>
                                        </tr>
                                        <tr class="bg-red">
                                            <td class="w-50 text-right tr-p">{{ __('payment.today_expense') }} (−)</td>
                                            <td class="w-50 text-right tr-p">{{ number_format($todayExpenses, 2) }}</td>
                                        </tr>
                                        <tr class="bg-blue">
                                            <td class="w-50 text-right tr-p">{{ __('payment.balance_cash_in_hand') }}</td>
                                            <td class="w-50 text-right tr-p">
                                                {{ number_format($totalIncome - $todayExpenses, 2) }}</td>
                                        </tr>
                                        <tr class="bg-yellow">
                                            <td class="w-50 text-right tr-p">
                                                <h4><b>{{ __('payment.today_closing_balance') }}</b></h4>
                                            </td>
                                            <td class="w-50 text-right tr-p">
                                                <h4><b>{{ number_format($totalIncome - $todayExpenses, 2) }}</b></h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        {{--
                            {{ __('payment.report_x_register_shift') }}
                        </a>
                        <a href="{{ route('transaction.report-z.print') }}" target="_blank" class="btn btn-success">
                            {{ __('payment.report_z_end_day') }}
                        </a>---}}
                        <button type="button" class="btn btn-danger close">
                            {{ __('app.accept_close_cash') }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--end page wrappe -->

@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/transaction/close-cash.js') }}"></script>
@endsection
