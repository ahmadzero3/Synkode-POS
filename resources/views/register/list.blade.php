@extends('layouts.app')
@section('title', __('register.list'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('custom/css/register/list.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['register.registers', 'register.list']" />

            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('register.list') }}</h5>
                    </div>

                    @can('register.create')
                        <x-anchor-tag href="{{ route('register.create') }}" text="{{ __('register.create_register') }}"
                            class="btn btn-primary px-5" />
                    @endcan
                </div>
                <div class="card-body">
                    <!-- added class register-table-wrapper -->
                    <div class="table-responsive register-table-wrapper">
                        <form class="row g-3 needs-validation" id="datatableForm" action="{{ route('register.delete') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')
                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"></th>
                                        <th><input class="form-check-input row-select" type="checkbox"></th>
                                        <th>{{ __('register.name') }}</th>
                                        <th>{{ __('register.code') }}</th>
                                        <th>{{ __('register.phone_number') }}</th>
                                        <th>{{ __('register.user') }}</th>
                                        <th>{{ __('register.note') }}</th>
                                        <th>{{ __('register.active') }}</th>
                                        <th>{{ __('app.created_by') }}</th>
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
    <script src="{{ versionedAsset('custom/js/register/registers-list.js') }}"></script>
@endsection
