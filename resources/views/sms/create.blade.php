@extends('layouts.app')
@section('title', __('message.create_sms'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'message.sms',
            'message.create_sms',
        ]" />
            <div class="row">
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('message.create_sms') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="smsForm" action="{{ route('sms.send') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <div class="col-md-12">
                                    <x-label for="mobile_numbers" name="{{ __('message.mobile_number') }}" />
                                    <x-input type="text" name="mobile_numbers" :required="true" value=""
                                        placeholder="{{ __('message.comma_separator_for_multiple_numbers') }}"
                                        :autofocus='true' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="message" name="{{ __('message.message') }}" />
                                    <x-textarea name="message" value="" textRows="10" />
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" class="primary px-4" text="{{ __('message.send') }}" />
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
    <script src="{{ versionedAsset('custom/js/sms/sms.js') }}"></script>
@endsection
