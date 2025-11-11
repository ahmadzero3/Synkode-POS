@extends('layouts.app')
@section('title', __('payment.update_bank_account'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'app.settings',
            'payment.bank_accounts',
            'payment.update_bank_account',
        ]" />
            <div class="row">
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('payment.bank_details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="paymentTypeForm"
                                action="{{ route('payment.type.update') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $paymentType->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('payment.bank_name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $paymentType->name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="account_number" name="{{ __('payment.account_number') }}" />
                                    <x-input type="text" name="account_number" value="{{ $paymentType->account_number }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="bank_code" name="{{ __('payment.bank_code') }}" />
                                    <x-input type="text" name="bank_code" value="{{ $paymentType->bank_code }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="description" name="{{ __('app.other_details') }}" />
                                    <x-textarea name="description" value="{{ $paymentType->description }}" />
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            {{ ($paymentType->print_bit) ? 'checked' : '' }} id="print_bit" name="print_bit">
                                        <label class="form-check-label" for="print_bit">
                                            {{ __('payment.print_bank_details_on_invoice') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12 d-none">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $paymentType->status }}" dropdownName='status' />
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
