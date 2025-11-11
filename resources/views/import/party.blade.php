@extends('layouts.app')
@section('title', __('party.import_contacts'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.utilities', 'party.import_contacts']" />
            <div class="row">
                <form class="row g-3 needs-validation" id="importForm" action="{{ route('import.party.upload') }}"
                    enctype="multipart/form-data">
                    {{-- CSRF Protection --}}
                    @csrf
                    @method('POST')

                    <input type="hidden" id="base_url" value="{{ url('/') }}">
                    <div class="col-12 col-lg-12">
                        @include('layouts.session')

                        <div class="card">
                            <div class="card-header px-4 py-3">
                                <h5 class="mb-0">{{ __('party.import_contacts') }}</h5>
                            </div>
                            <div class="card-body p-4 row g-3">
                                <div class="col-md-6">
                                    <x-label for="excel_file" name="{{ __('app.browse_file') }}" />
                                    <input class="form-control" type="file" id="excel_file" name="excel_file">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <a href="{{ route('download-contact-sheet') }}"
                                        class="btn btn-outline-primary px-5 radius-0" download>
                                        {{ __('app.download_sample') }}
                                    </a>
                                </div>
                            </div>

                            <div class="card-body p-4 row g-3">
                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                        <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                        <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}"
                                            class="btn btn-light px-4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!--end row-->
        </div>
    </div>
    <!-- Import Modals -->

@endsection

@section('js')
    @include('plugin.export-table')
    <script src="{{ versionedAsset('custom/js/import/party.js') }}"></script>
@endsection
