@extends('layouts.app')
@section('title', __('app.backup_database'))

@section('css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('custom/css/register/list.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="['app.backup_database', 'app.database_backup_list']" />

            <div class="card">
                <div class="card-header px-4 py-3 d-flex justify-content-between">
                    <div>
                        <h5 class="mb-0 text-uppercase">{{ __('app.backup_database') }}</h5>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary px-5" data-bs-toggle="modal"
                            data-bs-target="#createDatabaseBackupModal">
                            {{ __('app.create_database_backup') }}
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive register-table-wrapper">
                        <form class="row g-3 needs-validation" id="datatableForm"
                              action="{{ route('database.backup.delete') }}" enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            <table class="table table-striped table-bordered border w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th class="d-none"></th>
                                        <th><input class="form-check-input row-select" type="checkbox"></th>
                                        <th>{{ __('app.file_name') }}</th>
                                        <th>{{ __('app.size') }}</th>
                                        <th>{{ __('app.date') }}</th>
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

    @include('modals.database-backup.create-database-backup')
@endsection

@section('js')
    <script src="{{ versionedAsset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ versionedAsset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ versionedAsset('custom/js/common/common.js') }}"></script>

    {{-- ✅ Expose dynamic route to JS for correct URL resolution (fixes empty table) --}}
    <script>
        window.databaseBackupDatatableUrl = "{{ url('database-backup/datatable-list') }}";
    </script>

    <script src="{{ versionedAsset('custom/js/database-backup/database-backup-list.js') }}"></script>
@endsection
