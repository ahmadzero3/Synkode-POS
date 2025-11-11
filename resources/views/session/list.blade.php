@extends('layouts.app')
@section('title', __('app.session'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('custom/css/session/list.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.session', 'app.list_session']" />

            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('app.session') }}</h5>
                    </div>

                    @can('session.create')
                        <x-anchor-tag href="{{ route('session.create') }}" text="{{ __('app.create_session') }}"
                            class="btn btn-primary px-5" />
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive-lg session-table-wrapper">
                        <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('session.delete') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"></th>
                                        <th><input class="form-check-input row-select" type="checkbox"></th>
                                        <th>{{ __('app.User') }}</th>
                                        <th>{{ __('app.Start Time') }}</th>
                                        <th>{{ __('app.End Time') }}</th>
                                        <th>{{ __('app.Duration (Minutes)') }}</th>
                                        <th>{{ __('app.Created By') }}</th>
                                        <th>{{ __('app.created_at') }}</th>
                                        <th>{{ __('app.action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/session/session-list.js') }}"></script>
@endsection