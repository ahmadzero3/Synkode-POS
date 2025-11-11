@extends('layouts.app')
@section('title', __('currency.update_currency'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'app.settings',
            'currency.currencies',
            'currency.update_currency',
        ]" />
            <div class="row">
                <!-- 🔹 Changed from col-lg-6 to col-12 for full width -->
                <div class="col-12">
                    @include('layouts.session')

                    <!-- 🔹 Added w-100 to make card full width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('currency.details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="currencyForm" action="{{ route('currency.update') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $currency->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $currency->name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="code" name="{{ __('app.short_code') }}" />
                                    <x-input type="text" name="code" :required="true" placeholder="USD | INR | KWD"
                                        value="{{ $currency->code }}" :minlength=3 :maxlength=3 />
                                    <small class="text-end">
                                        Click here for <a href="https://taxsummaries.pwc.com/glossary/currency-codes"
                                            target="_blank">Currency code (ISO 4217)</a> use only<b>3 letters</b>.
                                    </small>
                                </div>

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('currency.symbol') }}" />
                                    <x-input type="text" name="symbol" :required="true" value="{{ $currency->symbol }}"
                                        :autofocus='false' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="exchange_rate" name="{{ __('currency.exchange_rate') }}" />
                                    <x-input type="text" additionalClasses="cu_numeric" name="exchange_rate"
                                        placeholder="0.00" :required="true" value="{{ $currency->exchange_rate }}"
                                        :autofocus='false' />
                                </div>

                                <div class="col-md-12 d-none">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_company_currency"
                                            name="is_company_currency" {{ ($currency->is_company_currency) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_company_currency">
                                            {{ __('currency.set_as_company_currency') }}
                                        </label>
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

@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/currency/currency.js') }}"></script>
@endsection