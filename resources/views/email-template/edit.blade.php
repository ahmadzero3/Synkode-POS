@extends('layouts.app')
@section('title', __('message.update_template'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
            'message.email',
            'message.email_templates',
            'message.update_template',
        ]" />
            <div class="row">
                <!-- 🔹 Changed from col-12 col-lg-6 to col-12 for full width -->
                <div class="col-12">
                    <!-- 🔹 Added w-100 to make card full width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('message.edit_template') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="templateForm"
                                action="{{ route('email.template.update') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')

                                <input type="hidden" name='id' value="{{ $template->id }}" />
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value="{{ $template->name }}"
                                        :autofocus='true' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="subject" name="{{ __('message.subject') }}" />
                                    <x-input type="text" name="subject" :required="true" value="{{ $template->subject }}" />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="content" name="{{ __('message.email_content') }}" />
                                    <x-textarea name="content" value="{!! ($template->content) !!}" textRows='10' />
                                </div>

                                <div class="col-md-12">
                                    <x-label for="content" name="{{ __('message.data_keys') }}" />
                                    <b>
                                        <p>{!! nl2br($template->keys) !!}</p>
                                    </b>
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
