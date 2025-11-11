@extends('layouts.app')
@section('title', __('language.create'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.settings', 'language.languages', 'language.create']" />
            <div class="row">
                <!-- 🔹 Changed from col-lg-6 to col-12 to make card full width -->
                <div class="col-12">
                    @include('layouts.session')
                    <!-- 🔹 Added w-100 to ensure the card stretches full width -->
                    <div class="card w-100">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('language.details') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="languageForm" action="{{ route('language.store') }}"
                                enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('POST')

                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                <div class="col-md-12">
                                    <x-label for="name" name="{{ __('app.name') }}" />
                                    <x-input type="text" name="name" :required="true" value=""
                                        :autofocus='true' />
                                </div>
                                <div class="col-md-12">
                                    <x-label for="code" name="{{ __('app.short_code') }}" />
                                    <x-input type="text" name="code" :required="true"
                                        placeholder="Ex: en (en indicate English)" value="" />
                                    <small class="text-end">
                                        Click here for <a href="https://www.science.co.il/language/Codes.php"
                                            target="_blank">Short Codes</a> use <b>Code 2</b> column.
                                    </small>
                                </div>

                                <div class="col-md-12">
                                    <x-label for="emoji" name="{{ __('app.country_flag') }}" />
                                    <div class="input-group">
                                        <x-input type="text" name="emoji" :required="true" value=""
                                            placeholder="Click on right side button" :readonly="true" />
                                        <button type="button" class="input-group-text" data-bs-toggle="modal"
                                            data-bs-target="#flagModal">
                                            <i class='text-primary bx bx-search-alt'></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <x-label for="direction" name="{{ __('app.direction') }}" />
                                    <x-dropdown-general optionNaming="appDirection" dropdownName='direction' />
                                </div>
                                <div class="col-md-12">
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

    <!-- Import Modals -->
    @include('modals.flag.flag')

@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/language/language.js') }}"></script>
@endsection
