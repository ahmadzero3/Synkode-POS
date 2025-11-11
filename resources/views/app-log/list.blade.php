@extends('layouts.app')
@section('title', __('app.app_log'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('custom/css/app-log/list.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.app_log', 'app.list']" />

            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('app.app_log') }}</h5>
                    </div>

                    @can('app.log.clear_all')
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" id="clearAllLogs">
                                <i class="bx bx-trash"></i> {{ __('app.clear_all_logs') }}
                            </button>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <!-- added class app-log-table-wrapper -->
                    <div class="table-responsive app-log-table-wrapper">
                        <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('app.log.delete') }}"
                              enctype="multipart/form-data">
                            @csrf
                            @method('POST')
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                <tr>
                                    <th class="d-none"></th>
                                    <th><input class="form-check-input row-select" type="checkbox"></th>
                                    <th>{{ __('app.type') }}</th>
                                    <th>{{ __('app.severity') }}</th>
                                    <th>{{ __('app.message') }}</th>
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

    @include('modals.app-log.log-details-modal')
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/app-log/app-log-list.js') }}"></script>
@endsection
