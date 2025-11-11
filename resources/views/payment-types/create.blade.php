@extends('layouts.app')
@section('title', __('payment.create'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'app.settings',
            'payment.bank_accounts',
            'payment.create_account',
        ]" />
            <div class="row">
                <!-- 🔹 Changed from col-6 col-lg-6 to col-12 for full width -->
                <div class="col-12">
                    <!-- 🔹 Added w-100 to make card stretch entire page width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('payment.bank_details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="paymentTypeForm"
                                action="{{ route('payment.type.store') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('payment.bank_name') }}" />
                                    <x-input type="text" name="name" :required="true" value="" :autofocus='true' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="account_number" name="{{ __('payment.account_number') }}" />
                                    <x-input type="text" name="account_number" value="" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="bank_code" name="{{ __('payment.bank_code') }}" />
                                    <x-input type="text" name="bank_code" value="" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="description" name="{{ __('app.other_details') }}" />
                                    <x-textarea name="description" value="" />
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="print_bit" name="print_bit">
                                        <label class="form-check-label" for="print_bit">
                                            {{ __('payment.print_bank_details_on_invoice') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12 d-none">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="" dropdownName='status' />
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
@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/payment-types/payment-type.js') }}"></script>
@endsection
