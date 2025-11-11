@extends('layouts.app')
@section('title', __('message.create_template'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'message.email',
            'message.email_templates',
        ]" />
            <div class="row">
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('message.create_template') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="templateForm"
                                action="{{ route('email.template.store') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="" :autofocus='true' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="subject" name="{{ __('message.subject') }}" />
                                    <x-input type="text" name="subject" :required="true" value=""
                                        placeholder="{{ __('message.subject') }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="content" name="{{ __('message.email_content') }}" />
                                    <x-textarea name="content" value="" textRows='10' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="keys" name="{{ __('message.data_keys') }}" />
                                    <x-textarea name="keys" value="" textRows='10' />
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
    <script src="{{ versionedAsset('custom/js/email-template/email-template.js') }}"></script>
@endsection
