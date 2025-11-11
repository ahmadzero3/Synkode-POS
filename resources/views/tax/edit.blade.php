@extends('layouts.app')
@section('title', __('tax.update_tax'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'tax.taxes',
            'tax.list',
            'tax.update_tax',
        ]" />
            <div class="row">
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('tax.details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="taxForm" action="{{ route('tax.update') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')
                                <input type="hidden" name='id' value="{{ $tax->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $tax->name }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="rate" name="{{ __('tax.rate') }}" />
                                    <x-input type="text" name="rate" :required="true" value="{{ $tax->rate }}" />
                                </div>

                                <div class="col-md-12 d-none">
                                    <x-label for="status" name="{{ __('app.status') }}" />
                                    <x-dropdown-status selected="{{ $tax->status }}" dropdownName='status' />
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
    <script src="{{ versionedAsset('custom/js/tax/tax.js') }}"></script>
@endsection