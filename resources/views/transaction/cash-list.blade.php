@extends('layouts.app')
@section('title', __('payment.cash_in_hand'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
@endsection

@section('content')

    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'payment.cash_and_bank',
            'payment.cash_in_hand',
        ]" />

            <div class="card">

                <div class="card-header px-4 py-3 d-flex align-items-center">
                    <!-- Left side content -->
                    <div>
                        <h5 class="mb-0 text-uppercase">
                            {{ __('payment.cash_in_hand') }} ({{ \Carbon\Carbon::now()->format('d-m-Y') }}) :
                            <span class="text-{{ $cashIncreaseSum >= 0 ? 'success' : 'danger' }} cash-in-hand">
                                {{ number_format((float) $cashIncreaseSum, 2) }}
                            </span>
                        </h5>
                    </div>

                    <div class="ms-auto me-3">
                        <h5 class="mb-0 text-uppercase">
                            {{ __('payment.total_cash') }} :
                            <span class="text-{{ $total_cash >= 0 ? 'success' : 'danger' }} total-cash">
                                {{ $total_cash }}
                            </span>
                        </h5>
                    </div>

                    <button role="button" class="btn btn-primary px-5 make-cash-adjustment">
                        {{ __('payment.cash_adjust') }}
                    </button>
                </div>


                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <x-label for="from_date" name="{{ __('app.from_date') }}" />
                            <a tabindex="0" class="text-primary" data-bs-toggle="popover" data-bs-trigger="hover focus"
                                data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
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
                                data-bs-content="Filter by Sale Date"><i class="fadeIn animated bx bx-info-circle"></i></a>
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
                                        <th class="d-none"><!-- Which Stores ID & it is used for sorting --></th>
                                        <th>{{ __('app.type') }}</th>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('party.name') }}</th>
                                        {{-- NEW: Register Name --}}
                                        <th>Register Name</th>
                                        <th>{{ __('payment.amount') }}</th>
                                        <th>{{ __('app.note') }}</th>
                                        <th>{{ __('app.created_by') }}</th>
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

    @include("modals.transaction.cash-in-hand")

@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/transaction/cash-list.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/modals/transaction/cash-in-hand.js') }}"></script>
@endsection
